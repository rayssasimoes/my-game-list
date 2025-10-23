<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Foto de Perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Atualize a foto de perfil da sua conta.') }}
        </p>
    </header>

    {{-- Exibe a foto de perfil atual --}}
    <div class="mt-6 text-center">
        @if (Auth::user()->profile_photo_path)
            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Foto de Perfil" class="rounded-full h-20 w-20 object-cover inline-block">
        @else
            <div class="rounded-full bg-gray-200 h-20 w-20 flex items-center justify-center inline-block">
                <span class="text-gray-500 text-xs">Sem Foto</span>
            </div>
        @endif
    </div>

    <form method="post" action="{{ route('profile.photo.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="photo" :value="__('Nova Foto de Perfil')" />
            <x-text-input id="photo" name="photo" type="file" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Salvar') }}</x-primary-button>

            @if (session('status') === 'profile-photo-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Salvo.') }}</p>
            @endif
        </div>
    </form>
</section>
