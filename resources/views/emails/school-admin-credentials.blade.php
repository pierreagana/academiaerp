<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0; padding:0; background-color:#F1F5F9; font-family: Arial, Helvetica, sans-serif; color:#1E293B;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F1F5F9; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #E2E8F0;">
                    <tr>
                        <td style="background-color:#031C5B; padding:28px 32px;">
                            <span style="color:#ffffff; font-size:18px; font-weight:bold;">AcademiaERP</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="font-size:18px; margin:0 0 12px;">Bienvenue, {{ $user->name }} !</h1>
                            <p style="font-size:14px; line-height:1.6; margin:0 0 20px; color:#475569;">
                                L'espace AcademiaERP de <strong>{{ $school->name }}</strong> a été créé. Voici vos identifiants de connexion :
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:16px 20px; font-size:13px; color:#64748B;">Code Établissement</td>
                                    <td style="padding:16px 20px; font-size:14px; font-weight:bold; text-align:right;">{{ $school->code }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 20px 16px; font-size:13px; color:#64748B;">Email</td>
                                    <td style="padding:0 20px 16px; font-size:14px; font-weight:bold; text-align:right;">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 20px 16px; font-size:13px; color:#64748B;">Mot de passe</td>
                                    <td style="padding:0 20px 16px; font-size:14px; font-weight:bold; text-align:right; font-family: monospace;">{{ $plainPassword }}</td>
                                </tr>
                            </table>

                            @php
                                $package = $school->activePackage();
                                $includedModules = $package
                                    ? array_values(array_unique(array_intersect($package->features ?? [], array_values(\App\Models\User::SLUG_MODULE_MAP))))
                                    : null;
                            @endphp
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#EEF2FF; border:1px solid #C7D2FE; border-radius:12px; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:0.05em; color:#4338CA; margin:0 0 8px;">
                                            Votre forfait : {{ $school->plan_name }}
                                        </p>
                                        @if($includedModules)
                                            <p style="font-size:13px; line-height:1.7; color:#3730A3; margin:0;">
                                                Modules inclus : {{ implode(' · ', $includedModules) }}
                                            </p>
                                        @else
                                            <p style="font-size:13px; line-height:1.7; color:#3730A3; margin:0;">
                                                Tous les modules sont accessibles pendant la mise en place de votre compte.
                                            </p>
                                        @endif
                                        <p style="font-size:12px; line-height:1.6; color:#4338CA; margin:10px 0 0;">
                                            Besoin de plus (Cantine, Transport, RH, Multi-Campus...) ? Demandez un changement de forfait depuis votre espace, rubrique « Forfait ».
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:13px; line-height:1.6; margin:0 0 24px; color:#94A3B8;">
                                Pour votre sécurité, nous vous recommandons de modifier ce mot de passe dès votre première connexion.
                            </p>

                            <a href="{{ route('login') }}" style="display:inline-block; background-color:#031C5B; color:#ffffff; text-decoration:none; font-size:14px; font-weight:bold; padding:12px 24px; border-radius:10px;">
                                Se connecter à mon espace
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
