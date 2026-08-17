@extends('installer.layout')

@section('content')
    <h1 class="text-2xl font-bold mb-2">Create admin account</h1>
    <p class="text-slate-600 mb-6">This account will have full access to the admin panel.</p>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="post" action="{{ route('installer.save_admin') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Full name</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                   placeholder="John Doe">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required pattern="[a-zA-Z0-9_-]+"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                       placeholder="admin">
                <div class="text-xs text-slate-500 mt-1">Used to log in</div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                       placeholder="you@example.com">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <div class="text-xs text-slate-500 mt-1">Minimum 8 characters</div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Confirm password</label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <a href="{{ route('installer.database') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800">← Back</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">
                Create account →
            </button>
        </div>
    </form>
@endsection
