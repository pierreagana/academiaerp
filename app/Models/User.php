<?php

namespace App\Models;

use App\Modules\Academic\Domain\Models\Role;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Teacher;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'login_id',
        'password',
        'role',
        'role_id',
        'teacher_id',
        'staff_id',
        'school_id',
        'current_branch_id',
        'view_all_branches',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'view_all_branches' => 'boolean',
        ];
    }

    /**
     * Check if the user is a Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Get the school that the user belongs to.
     */
    public function school()
    {
        return $this->belongsTo(\App\Modules\SuperAdmin\Domain\Models\School::class, 'school_id');
    }

    /**
     * Get the RBAC role assigned to this user (distinct from the coarse `role` string column).
     */
    public function assignedRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /** School groups this user founded — each with its own school portfolio. A person can found more than one group. */
    public function foundedGroups()
    {
        return $this->hasMany(\App\Modules\SuperAdmin\Domain\Models\SchoolGroup::class, 'founder_user_id');
    }

    public function isFounder(): bool
    {
        return $this->foundedGroups()->exists();
    }

    /** Every school across every group this user founded — the real cross-school portfolio, not just their own school_id. */
    public function foundedSchools()
    {
        return \App\Modules\SuperAdmin\Domain\Models\School::whereIn(
            'school_group_id',
            $this->foundedGroups()->pluck('id')
        );
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function staffMember()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function currentBranch()
    {
        return $this->belongsTo(\App\Modules\Academic\Domain\Models\Branch::class, 'current_branch_id');
    }

    /**
     * A branch-director portal account (a Staff member whose assigned Role is flagged
     * `is_branch_director`) is permanently scoped to the branch their employment record
     * belongs to — they never switch branches or use Vue Globale.
     */
    public function isBranchDirector(): bool
    {
        return (bool) $this->assignedRole?->is_branch_director;
    }

    /**
     * The branch id whose data should be shown:
     * - branch directors are pinned to their own Staff record's branch,
     * - "Vue Globale" (view_all_branches) returns null, meaning "no branch filter, show everything",
     * - otherwise falls back to the school's main branch when nothing was explicitly switched.
     */
    public function activeBranchId(): ?int
    {
        if ($this->isBranchDirector()) {
            return $this->staffMember?->branch_id;
        }

        if ($this->view_all_branches) {
            return null;
        }

        if ($this->current_branch_id) {
            return $this->current_branch_id;
        }

        return \App\Modules\Academic\Domain\Models\Branch::where('school_id', $this->school_id)
            ->where('is_main', true)
            ->value('id');
    }

    /**
     * $action is one of the 5 CRUD actions on the role's permission matrix
     * (show/create/edit/update/delete). Defaults to 'show' — the baseline
     * "this role has the module at all" check used by page-level route gates.
     */
    public function hasPermission(string $slug, string $action = 'show'): bool
    {
        if (!$this->role_id) {
            return false;
        }

        $permission = $this->assignedRole?->permissions->firstWhere('slug', $slug);
        if (!$permission) {
            return false;
        }

        return (bool) ($permission->pivot->{"can_{$action}"} ?? false);
    }

    /**
     * Maps each permission slug to the real SaaS module it belongs to
     * (matching the module names stored in saas_modules / SaasPackage::features).
     * Kept in sync with database/seeders/PermissionSeeder.php.
     */
    public const SLUG_MODULE_MAP = [
        'academic.classes.manage'      => 'Académie de Base',
        'academic.subjects.manage'     => 'Académie de Base',
        'academic.rooms.manage'        => 'Académie de Base',
        'academic.semesters.manage'    => 'Académie de Base',
        'academic.languages.manage'    => 'Académie de Base',
        'academic.syllabuses.manage'   => 'Académie de Base',
        'academic.timetable.manage'    => 'Académie de Base',
        'academic.cards.manage'        => 'Cartes & Diplômes',
        'academic.students.manage'     => 'Étudiants & Tuteurs',
        'academic.parents.manage'      => 'Étudiants & Tuteurs',
        'academic.teachers.manage'     => 'Enseignants & Personnel',
        'academic.personnel.manage'    => 'Enseignants & Personnel',
        'academic.awards.manage'       => 'Académie de Base',
        'academic.presence.manage'     => "Présence & Contrôle d'Accès",
        'finance.fees.manage'          => 'Frais Scolaires',
        'finance.scholarships.manage'  => 'Bourses',
        'finance.expenses.manage'      => 'Dépenses & Budgets',
        'communication.events.manage'  => 'Communication & Événements',
        'library.manage'               => 'Bibliothèque',
        'canteen.manage'               => 'Cantine',
        'infirmary.manage'             => 'Infirmerie',
        'transport.manage'             => 'Transport',
        'hr.manage'                    => 'RH & Paie',
        'branches.manage'              => 'Multi-Succursales',
        'report-card.manage'           => 'Livret Scolaire',
        'academic.bulletins.manage'    => 'Bulletins de Notes',
        'academic.homework.manage'     => 'Devoirs & Interrogations',
    ];

    /**
     * Whether the school this user belongs to is subscribed to a real package
     * that includes the module behind this permission slug — or has a
     * separately approved paid extension for it. Fails open (true) whenever
     * there's nothing concrete to restrict against — an unmapped slug, no
     * school, or a plan_name that doesn't match a real SaasPackage — so this
     * only ever tightens access once a school actually has a recognized
     * package, per product intent.
     */
    public function schoolHasModuleAccess(string $slug): bool
    {
        $moduleName = self::SLUG_MODULE_MAP[$slug] ?? null;
        if (!$moduleName) {
            return true;
        }

        $school = $this->school;
        $package = $school?->activePackage();
        if (!$package) {
            return true;
        }

        $features = is_array($package->features) ? $package->features : [];
        if (in_array($moduleName, $features, true)) {
            return true;
        }

        return in_array($moduleName, $school->approvedExtensionModuleNames(), true);
    }

    /**
     * Directors/superadmins always have full access. Branch directors have full access
     * except for managing succursales themselves. Other portal accounts (teacher/staff)
     * are gated by their assigned RBAC role's permissions. In every case, access is
     * also capped by whether the school's subscribed package includes that module.
     */
    public function canAccess(string $slug, string $action = 'show'): bool
    {
        if ($this->role === 'superadmin') {
            return true;
        }

        if (!$this->schoolHasModuleAccess($slug)) {
            return false;
        }

        if ($this->role === 'adminschool') {
            return true;
        }

        if ($this->isBranchDirector()) {
            return $slug !== 'branches.manage';
        }

        return $this->hasPermission($slug, $action);
    }
}
