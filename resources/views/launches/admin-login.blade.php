@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6">
        <a href="{{ route('home') }}" class="mb-5 inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar para o site</a>
        <h1 class="text-2xl font-bold">Admin de lançamentos</h1>
        <p class="mt-1 text-sm text-slate-500">Acesse para gerenciar vendedoras e lançamentos.</p>

        <form action="{{ route('launches.admin.login.store') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="text-sm font-semibold text-slate-800">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
            </div>
            <div>
                <label for="password" class="text-sm font-semibold text-slate-800">Senha</label>
                <input id="password" name="password" type="password" required class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
            </div>
            <button class="h-11 w-full rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">Entrar</button>
        </form>
    </div>
@endsection
