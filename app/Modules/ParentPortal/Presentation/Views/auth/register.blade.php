<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Parent - Academia ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-10 h-10 rounded-xl bg-[#031C5B] text-white flex items-center justify-center font-extrabold text-lg">A</div>
            <span class="font-extrabold text-[#031C5B] text-[18px]">Espace Parent</span>
        </div>
        <h1 class="text-[24px] font-bold text-slate-900 mb-1">Créer un compte</h1>
        <p class="text-[13.5px] text-slate-500 mb-6">Votre compte n'est rattaché à aucune école pour l'instant — vous ajouterez vos enfants juste après.</p>

        @if($errors->any())
        <div class="p-3 mb-4 text-[13px] text-red-800 rounded-lg bg-red-50 border border-red-100">
            @foreach($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
        @endif

        <form action="{{ route('parent.register.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Nom complet</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Ex: Jean Dupont"
                    class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
            </div>
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Numéro de téléphone</label>
                @include('SchoolDashboard::components.phone-input', [
                    'required' => true,
                    'selectClass' => 'w-[110px] bg-slate-50 border border-slate-200 text-slate-900 text-[13px] rounded-xl px-2 py-3 outline-none focus:border-[#031C5B] cursor-pointer',
                    'inputClass' => 'flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]',
                ])
                <p class="text-[11.5px] text-slate-400 mt-1">Doit correspondre au numéro déjà déclaré à l'établissement de votre enfant.</p>
            </div>
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Adresse email (optionnel)</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="jean.dupont@exemple.com"
                    class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
            </div>
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Mot de passe</label>
                <input type="password" name="password" required placeholder="8 caractères minimum"
                    class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
            </div>
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••"
                    class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
            </div>
            <button type="submit" class="w-full bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[14px] py-3 rounded-xl transition">
                Créer mon compte
            </button>
        </form>
        <p class="text-[12.5px] text-slate-400 mt-6 text-center">
            Déjà un compte ? <a href="{{ route('parent.login') }}" class="text-[#031C5B] font-bold hover:underline">Se connecter</a>
        </p>
    </div>
</body>
</html>
