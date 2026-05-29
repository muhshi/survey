<div class="space-y-3">
    @if($users->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400 italic text-center py-4">
            Belum ada anggota di kelompok ini.
        </p>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
            Total: <span class="font-semibold text-gray-900 dark:text-white">{{ $users->count() }}</span> anggota
        </p>
        <div class="border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 sticky top-0">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-600 dark:text-gray-400">#</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-600 dark:text-gray-400">Nama</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-600 dark:text-gray-400">Email</th>
                        <th class="text-right px-4 py-2.5 font-medium text-gray-600 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($users as $index => $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 tabular-nums">{{ $index + 1 }}</td>
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <button
                                    type="button"
                                    wire:click="removeMember({{ $user->id }}, {{ $group->id }})"
                                    wire:confirm="Hapus {{ $user->name }} dari kelompok ini?"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-danger-600 hover:text-danger-500 dark:text-danger-400 dark:hover:text-danger-300 transition-colors disabled:opacity-50"
                                >
                                    <x-heroicon-m-x-mark class="w-4 h-4" />
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
