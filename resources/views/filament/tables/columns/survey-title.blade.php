@php
    $record = $getRecord();
    $kategori = $record->kategori?->name ?? 'Umum';
    $title = $record->title;
    $slug = $record->slug;
    $url = $record->getPublicUrl();
    $isQuiz = (bool) $record->is_quiz;
@endphp

<div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 6px 0; width: 100%;" x-data="{
    copied: false,
    copy(url) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).catch(() => this.fallbackCopy(url));
        } else {
            this.fallbackCopy(url);
        }
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
        if (window.FilamentNotification) {
            new FilamentNotification().title('Tautan survei disalin ke clipboard!').success().send();
        }
    },
    fallbackCopy(text) {
        const el = document.createElement('textarea');
        el.value = text;
        el.style.position = 'fixed';
        el.style.left = '-9999px';
        el.style.top = '-9999px';
        document.body.appendChild(el);
        el.focus();
        el.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(el);
    }
}">
    <!-- Info Survei (Kiri) -->
    <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0; flex: 1;">
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <span style="font-size: 10px; font-weight: 600; background: rgba(148, 163, 184, 0.15); color: #475569; padding: 1px 6px; border-radius: 4px;" class="dark:text-slate-300">
                📁 {{ $kategori }}
            </span>
            @if($isQuiz)
                <span style="font-size: 10px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 4px;" class="dark:bg-amber-950/60 dark:text-amber-300">
                    Kuis
                </span>
            @endif
        </div>
        <div style="font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.35; word-break: break-word;" class="dark:text-slate-100">
            {{ $title }}
        </div>
        <div style="font-size: 11px; font-family: monospace; color: #0284c7; background: rgba(2, 132, 199, 0.08); padding: 1px 6px; border-radius: 4px; display: inline-block; width: fit-content; border: 1px solid rgba(2, 132, 199, 0.18);" class="dark:text-sky-300 dark:bg-sky-950/40">
            /s/{{ $slug }}
        </div>
    </div>

    <!-- Tombol Icon Aksi (Kanan - Tersusun Vertikal) -->
    <div style="display: flex; flex-direction: column; align-items: center; gap: 4px; flex-shrink: 0;" @click.stop="">
        <!-- Tombol Copy (Atas) -->
        <button
            type="button"
            @click.stop.prevent="copy('{{ $url }}')"
            :title="copied ? 'Tersalin!' : 'Salin Tautan Survei'"
            style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; cursor: pointer; transition: all 0.15s ease;"
            :style="copied ? 'background: #dcfce7; border-color: #86efac; color: #16a34a;' : 'color: #64748b;'"
            class="dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400"
            onmouseover="if(!this.style.background.includes('dcfce7')) { this.style.color='#0284c7'; this.style.borderColor='#0284c7'; this.style.background='rgba(2, 132, 199, 0.08)'; }"
            onmouseout="if(!this.style.background.includes('dcfce7')) { this.style.color='#64748b'; this.style.borderColor='#cbd5e1'; this.style.background='#ffffff'; }"
        >
            <template x-if="!copied">
                <x-filament::icon icon="heroicon-o-clipboard-document" class="fi-icon" style="width: 15px; height: 15px;" />
            </template>
            <template x-if="copied">
                <x-filament::icon icon="heroicon-o-check" class="fi-icon" style="width: 15px; height: 15px; color: #16a34a;" />
            </template>
        </button>

        <!-- Tombol Buka Tab Baru (Bawah) -->
        <a
            href="{{ $url }}"
            target="_blank"
            rel="noopener noreferrer"
            @click.stop=""
            title="Buka Survei di Tab Baru"
            style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #64748b; text-decoration: none; cursor: pointer; transition: all 0.15s ease;"
            class="dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400"
            onmouseover="this.style.color='#0284c7'; this.style.borderColor='#0284c7'; this.style.background='rgba(2, 132, 199, 0.08)';"
            onmouseout="this.style.color='#64748b'; this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';"
        >
            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="fi-icon" style="width: 15px; height: 15px;" />
        </a>
    </div>
</div>
