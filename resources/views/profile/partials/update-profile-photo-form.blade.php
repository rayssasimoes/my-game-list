<section>
    <header>
        <h2 class="h5">
            {{ __('Foto de Perfil') }}
        </h2>

        <p class="mt-1 text-muted">
            {{ __('Atualize a foto de perfil da sua conta.') }}
        </p>
    </header>

    {{-- Exibe a foto de perfil atual --}}
    <div class="mt-4 text-center">
        @if (Auth::user()->profile_photo_path)
            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Foto de Perfil" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover; margin: auto;">
        @else
            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; margin: auto;">
                <span class="text-white small">Sem Foto</span>
            </div>
        @endif
    </div>

    <form method="post" action="{{ route('profile.photo.update') }}" class="mt-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="photo" class="form-label">{{ __('Nova Foto de Perfil') }}</label>
            <input id="photo" name="photo" type="file" class="form-control @error('photo') is-invalid @enderror">
            @error('photo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-4">
            <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>

            @if (session('status') === 'profile-photo-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="small text-muted"
                >{{ __('Salvo.') }}</p>
            @endif
        </div>
    </form>
</section>