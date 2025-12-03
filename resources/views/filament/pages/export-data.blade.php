<x-filament::page>
    <div class="space-y-6">
        {{-- Card Principal --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">📊 Exportação de Dados</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Exporte dados do sistema em diferentes formatos. Selecione o tipo de dados, período e formato desejado.
            </p>

            {{ $this->form }}

            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <x-filament::button wire:click="exportar" color="primary" size="lg">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-2" />
                    Gerar e Baixar Arquivo
                </x-filament::button>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                    ⚡ O arquivo será gerado e baixado automaticamente.
                    📁 Formatos disponíveis: CSV (Excel), TXT e JSON.
                </p>
            </div>
        </div>

        {{-- Informações sobre Tipos de Exportação --}}
        <div class="bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800 p-6">
            <h3 class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-2">💡 Tipos de Exportação</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="font-medium text-gray-900 dark:text-gray-100">👥 Pacientes Completo</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Todos os dados dos pacientes com estatísticas</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="font-medium text-gray-900 dark:text-gray-100">📋 Pacientes Simples</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Lista básica de pacientes</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="font-medium text-gray-900 dark:text-gray-100">🔄 Atendimentos</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Registro completo de atendimentos</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="font-medium text-gray-900 dark:text-gray-100">📅 Consultas</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Agendamentos e consultas</p>
                </div>
            </div>
        </div>

        {{-- Dicas de Uso --}}
        <div class="bg-success-50 dark:bg-success-900/20 rounded-xl border border-success-200 dark:border-success-800 p-6">
            <h3 class="text-lg font-semibold text-success-900 dark:text-success-100 mb-2">✅ Como Usar</h3>
            <ul class="space-y-2 text-sm text-success-800 dark:text-success-200">
                <li class="flex items-start">
                    <x-heroicon-o-check-circle class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" />
                    <span>Selecione o tipo de dados que deseja exportar</span>
                </li>
                <li class="flex items-start">
                    <x-heroicon-o-check-circle class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" />
                    <span>Defina o período desejado (padrão: último mês)</span>
                </li>
                <li class="flex items-start">
                    <x-heroicon-o-check-circle class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" />
                    <span>Escolha o formato do arquivo (recomendado: CSV para Excel)</span>
                </li>
                <li class="flex items-start">
                    <x-heroicon-o-check-circle class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" />
                    <span>Clique em "Gerar e Baixar Arquivo"</span>
                </li>
            </ul>
        </div>

        {{-- Formatos Disponíveis --}}
        <div class="bg-info-50 dark:bg-info-900/20 rounded-xl border border-info-200 dark:border-info-800 p-6">
            <h3 class="text-lg font-semibold text-info-900 dark:text-info-100 mb-2">📁 Formatos Disponíveis</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                    <div class="text-2xl mb-2">📊</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">CSV</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Compatível com Excel e Google Sheets</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                    <div class="text-2xl mb-2">📝</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">TXT</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Texto simples, fácil de ler</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                    <div class="text-2xl mb-2">🔧</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">JSON</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Estrutura de dados para APIs</p>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>