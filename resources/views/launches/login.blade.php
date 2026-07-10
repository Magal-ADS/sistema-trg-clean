@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-950">Login</h1>
            <p class="mt-1 text-sm text-slate-500">Entre com seu e-mail e senha. O sistema identifica automaticamente se o acesso e de admin ou vendedor.</p>
        </div>

        <form action="{{ route('launches.login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="text-sm font-semibold text-slate-800">E-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                    class="mt-2 h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary-soft"
                >
            </div>

            <div>
                <label for="password" class="text-sm font-semibold text-slate-800">Senha</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="mt-2 h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary-soft"
                >
            </div>

            <button type="submit" class="h-11 w-full rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">
                Entrar
            </button>
        </form>
    </div>
@endsection
