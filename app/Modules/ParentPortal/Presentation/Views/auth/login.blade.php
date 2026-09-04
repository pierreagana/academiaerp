<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Parent - Academia ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-10 h-10 rounded-xl bg-[#031C5B] text-white flex items-center justify-center font-extrabold text-lg">A</div>
            <span class="font-extrabold text-[#031C5B] text-[18px]">Espace Parent</span>
        </div>
        <h1 class="text-[24px] font-bold text-slate-900 mb-1">Connexion</h1>
        <p class="text-[13.5px] text-slate-500 mb-6">Accédez au suivi de vos enfants, quelle que soit leur école.</p>

        @if($errors->any())
        <div class="p-3 mb-4 text-[13px] text-red-800 rounded-lg bg-red-50 border border-red-100">
            @foreach($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
        @endif

        <form action="{{ route('parent.login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Numéro de téléphone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required autofocus placeholder="Ex: 0102030405"
                    class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
            </div>
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••"
                    class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
            </div>
            <button type="submit" class="w-full bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[14px] py-3 rounded-xl transition">
                Se connecter
            </button>
        </form>
        <p class="text-[12.5px] text-slate-400 mt-6 text-center">
            Pas encore de compte ? <a href="{{ route('parent.register') }}" class="text-[#031C5B] font-bold hover:underline">Créer un compte</a>
        </p>
    </div>
</body>
</html>
