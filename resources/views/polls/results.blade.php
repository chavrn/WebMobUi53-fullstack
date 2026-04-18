<x-default-layout>
    <x-slot:title>
        Resultats du sondage
    </x-slot>

    <x-slot:description>
        Page de test pour verifier le branchement Vue.js sur les resultats d'un sondage.
    </x-slot>

    <x-slot:scripts>
        @vite(['resources/js/poll-results.js'])
    </x-slot>

    <div id="poll-results-app"></div>
</x-default-layout>
