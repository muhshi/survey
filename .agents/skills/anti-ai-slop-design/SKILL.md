---
name: anti-ai-slop-design
description: "Anti AI-Slop UI & Web Design: Panduan desain antarmuka otentik, profesional, dan berkelas. Gunakan skill ini setiap kali merancang, memoles, merefaktor, atau menulis kode frontend (Blade, HTML, CSS, Tailwind, Vue, React, JavaScript), landing page, form runner, maupun komponen UI. Mencegah template AI murahan (dark mode neon pekat, radial glow ungu/cyan, emoji acak di heading, glassmorphism berlebihan) dan menegakkan standar desain: design token semantik, light-mode institusional yang bersih, tipografi proporsional, alur layout alami, dan micro-copy otentik."
license: MIT
metadata:
  author: bps-demak
---

# Anti AI-Slop UI & Authentic Web Design

Panduan standar arsitektur frontend dan desain visual untuk mencegah jebakan **"AI Slop"** (template murahan khas LLM) dan menghasilkan antarmuka yang kredibel, otentik, elegan, dan profesional.

---

## 1. Anatomi "AI Slop" vs Desain Otentik (Crafted Design)

| Elemen | ❌ AI Slop (JANGAN PERNAH LAKUKAN) | ✅ Desain Otentik / Crafted (STANDAR KITA) |
| :--- | :--- | :--- |
| **Tema & Latar** | Latar hitam pekat / navy gelap gulita (`#0f172a`), radial gradient ungu/cyan mencolok di tengah layar, aura glow neon. | **Light mode institusional sebagai default** (`#f4f7fb`, surface `#ffffff`), bersih, nyaman dibaca lama. Dark mode disediakan secara presisi dan tidak murahan. |
| **Konteks Produk** | Template SaaS / Web3 crypto generik diterapkan membabi buta ke semua proyek (termasuk portal instansi pemerintah/sekolah). | **Desain berakar pada konteks instansi/domain** (misal BPS: resmi, kredibel, data-centric, ramah responden). |
| **Heading & Teks** | Menyelipkan emoji di heading (`📋 Kuesioner Digital`, `🚀 Mulai Sekarang`, `💡 Fitur`). | **Tipografi berbobot** tanpa emoji di judul. Gunakan SVG icon minimalis, badge status terukur, atau eyebrow pill jika butuh visual anchor. |
| **Arsitektur CSS** | Inline style acak, selector `!important`, CSS hack `position: absolute; top: 0; left: 0;` yang menabrak navbar/footer. | **Design Token semantik** (`--bg`, `--surface`, `--border`, `--text`, `--primary`, `--radius-*`) dan alur dokumen alami (`margin: 0 auto; max-width: ...`). |
| **Visual Hero** | Teks rata tengah membosankan dengan 1 tombol besar dan 3 kartu mengambang statis (*floating cards*). | **Split-hero informatif** (teks tajam di satu sisi + **mockup interaktif produk nyata** di sisi lainnya). |
| **Micro-copy** | Kalimat pemasaran generik ("Solusi terdepan masa kini", "Cepat dan terpercaya"). | **Kosakata nyata domain kerja** (misal BPS: "Rekrutmen Mitra Pendataan", "Kuis Pelatihan SPKRT/Susenas", "Evaluasi Kegiatan"). |

---

## 2. Sistem Design Token Semantik (CSS Variables)

Setiap halaman atau layout mandiri **WAJIB** mendefinisikan dan menggunakan token semantik. Jangan gunakan nilai hex acak berulang-ulang di seluruh file.

```css
:root {
    --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    --font-display: 'Outfit', 'Inter', sans-serif;

    /* Backgrounds & Surfaces */
    --bg: #f4f7fb;
    --bg-soft: #eaf1f9;
    --surface: #ffffff;
    --surface-2: #f8fafd;
    --surface-3: #eef4fb;
    --nav-bg: rgba(255, 255, 255, 0.88);

    /* Borders */
    --border: #e3eaf3;
    --border-strong: #cbd8e8;

    /* Text Hierarchy */
    --text: #0f1d33;        /* Keterbacaan kontras tinggi */
    --text-2: #53627c;      /* Teks sekunder/deskripsi */
    --text-3: #8593a9;      /* Metadata, caption, placeholder */

    /* Brand Primary & Accents (Contoh: BPS Demak Blue) */
    --primary: #0b6bbd;
    --primary-600: #0a5a9e;
    --primary-700: #08497f;
    --primary-50: #e9f2fc;
    --primary-100: #d5e8f9;

    --accent: #f59e0b;      /* Aksen hangat/peringatan */
    --accent-50: #fff6e5;

    --success: #0f9f6e;     /* Status sukses/aktif */
    --success-50: #e6f8f0;
    --danger: #dc2626;      /* Error/bahaya */
    --danger-50: #fdecec;

    /* Layered Shadows (Halus & Alami) */
    --shadow-sm: 0 1px 2px rgba(15, 29, 51, 0.06);
    --shadow-md: 0 10px 30px -18px rgba(15, 29, 51, 0.28);
    --shadow-lg: 0 28px 70px -32px rgba(15, 29, 51, 0.35);

    /* Radii */
    --radius-sm: 10px;
    --radius-md: 14px;
    --radius-lg: 20px;
    --radius-xl: 28px;

    color-scheme: light;
}

[data-theme="dark"] {
    --bg: #080f1c;
    --bg-soft: #0b1526;
    --surface: #0f1a2b;
    --surface-2: #142136;
    --surface-3: #182741;
    --nav-bg: rgba(8, 15, 28, 0.88);

    --border: #1e2c44;
    --border-strong: #2b3d5c;

    --text: #edf3ff;
    --text-2: #a7b7d0;
    --text-3: #7c8ea9;

    --primary: #4da8e6;
    --primary-600: #7cc3f2;
    --primary-700: #a6d8f9;
    --primary-50: #0e2438;
    --primary-100: #13314c;

    color-scheme: dark;
}
```

### Script Anti-Flicker Wajib di `<head>`
Jika aplikasi mendukung dark mode toggle, letakkan skrip inline ini di `<head>` sebelum stylesheet dirender untuk mencegah layar berkedip putih saat refresh:
```html
<script>
    (function () {
        var theme = 'light';
        try {
            var saved = localStorage.getItem('app-theme');
            if (saved === 'dark' || saved === 'light') theme = saved;
        } catch (e) {}
        document.documentElement.setAttribute('data-theme', theme);
    })();
</script>
```

---

## 3. Aturan Layout: Bekerja Sama dengan Alur Dokumen (No Hacks)

1. **Dilarang menggunakan `position: absolute; top: 0; left: 0; min-height: 100vh;`** untuk membungkus konten utama halaman:
   - Ini merusak susunan hierarki layout, menyebabkan konten tertutup navbar tetap (*fixed navbar*), dan menyembunyikan footer.
2. **Gunakan Layout Modifier dan Normal Flow**:
   - Manfaatkan section Blade untuk mengoper kelas body jika halaman butuh perlakuan khusus:
     ```blade
     @section('body_class', 'survey-page')
     @section('hide_footer', true)
     ```
   - Gunakan container terpusat dengan padding proporsional:
     ```css
     .runner {
         max-width: 880px;
         margin: 0 auto;
         padding: 26px 24px 72px;
     }
     ```

---

## 4. Tipografi & Kontras: Kredibilitas Instansional

1. **Pasangan Font**:
   - `Display / Headings`: Gunakan font yang tegas dan berkarakter (misal `Outfit`, `Plus Jakarta Sans`, atau font sans-serif modern).
   - `Body / Konten`: Gunakan font dengan legibilitas tinggi untuk membaca formulir dan teks panjang (`Inter`, `system-ui`).
2. **Penerapan Text Wrapping**:
   - Gunakan `text-wrap: balance;` pada heading (`h1`, `h2`, hero title).
   - Gunakan `text-wrap: pretty;` pada lead text atau paragraf pengantar.
   - Gunakan `font-size: clamp(2rem, 4vw, 3.2rem); line-height: 1.08; letter-spacing: -0.03em;` untuk heading utama.

---

## 5. Komponen Hero & Mockup Interaktif

Jangan mendesain hero yang hanya berupa teks dan tombol hampa. Tampilkan cuplikan interaktif nyata dari apa yang ditawarkan aplikasi:

1. **Eyebrow Status Pill**:
   ```html
   <div class="eyebrow">
       <span class="status-dot"></span>
       BPS KABUPATEN DEMAK • PORTAL FORM DIGITAL
   </div>
   ```
2. **Interactive Preview Mockup**:
   - Buat card preview kuesioner dengan header resmi, progres pengisian (`75% selesai`), butir pertanyaan riil dengan radio button atau checkbox yang bisa diklik.
   - Ini membuktikan fungsionalitas dan membangun rasa percaya pengguna seketika.

---

## 6. Checklist Verifikasi Anti AI-Slop Sebelum Selesai

Sebelum menganggap pekerjaan UI selesai, periksa checklist ini:
- [ ] Apakah latar belakangnya bukan sekadar gradien hitam pekat + neon ungu/cyan murahan?
- [ ] Apakah heading bebas dari emoji dekoratif yang tidak perlu?
- [ ] Apakah semua warna latar, teks, border, dan tombol memakai variabel/token CSS semantik?
- [ ] Apakah light mode terlihat sangat bersih, kontras, dan profesional?
- [ ] Jika ada dark mode, apakah transisinya mulus dan tidak merusak keterbacaan teks?
- [ ] Apakah layout tidak menggunakan hack `position: absolute` untuk seluruh viewport?
- [ ] Apakah micro-copy menggunakan istilah riil domain pengguna (bukan jargon AI umum)?
