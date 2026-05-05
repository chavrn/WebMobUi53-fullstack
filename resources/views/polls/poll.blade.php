<x-default-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-public-integrated.js'])
    </x-slot>

    <x-slot:title>
        Sondage public
    </x-slot>

    <div
        id="app"
        data-props='@json([
            "token" => $token,
            "loginUrl" => route("login")
        ])'
    ></div>
</x-default-layout>
