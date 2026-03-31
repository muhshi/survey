# Product Requirements Document (PRD): Sistem Manajemen Survei Dinamis

## 1. Executive Summary

- **Problem Statement**: Terdapat kebutuhan untuk sebuah platform survei dinamis di mana kuesioner dapat dibuat, dikategorikan, divalidasi, dan dianalisis hasilnya secara spesifik per kuesioner, tanpa harus melakukan *hardcode* pada setiap formulir.
- **Proposed Solution**: Membangun *Dynamic Survey Platform* menggunakan kerangka Laravel Filament untuk panel administrasi (*backend*) dan integrasi **SurveyJS** untuk pembuatan form (*Creator*) serta pengisian kuesioner (*Runner*). Sistem ini menyediakan kategorisasi dan dasbor analitik terisolasi untuk tiap jenis/kategori survei.
- **Success Criteria**:
  - Admin dapat merancang kuesioner kompleks (logika, validasi) tanpa menyentuh kode 100% berbasis *drag-and-drop* UI (JSON Schema).
  - Kuesioner dapat dikelompokkan secara dinamis ke dalam kategori yang tidak terbatas.
  - Hasil (*submission*) setiap kuesioner dapat diproses secara independen dengan visualisasi analitik (dasbor) spesifik.

## 2. User Experience & Functionality

- **User Personas**:
  - **Admin / Surveyor**: Bertugas membuat kategori, mendesain kuesioner menggunakan *SurveyJS Creator*, menerbitkan survei, dan melihat hasil analitik.
  - **Respondent**: Target pengguna yang mengisi kuesioner (TBD: Apakah publik atau *authenticated user*).
- **User Stories**:
  - *As an Admin*, saya ingin membuat struktur kuesioner secara visual sehingga tidak perlu meminta bantuan *programmer*.
  - *As an Admin*, saya ingin mengelompokkan kuesioner ke dalam berbagai kategori yang bisa saya tambah/ubah sendiri agar sistem tertata rapi.
  - *As an Admin*, saya ingin memiliki modul dasbor terpisah untuk hasil kuesioner tertentu sehingga analisis datanya spesifik dan relevan.
  - *As a Respondent*, saya ingin mengisi form dengan *interface* interaktif dan validasi *real-time* sehingga tahu jika ada data wajib yang terlewat.
- **Acceptance Criteria**:
  - Kuesioner tersimpan dalam format standar abstraksi JSON dari SurveyJS.
  - Menu CRUD *Category* terintegrasi di dalam panel Filament.
  - Modul *Dashboard* memiliki fleksibilitas untuk membaca *query* khusus berdasarkan JSON *Responses* dari setiap *Survey ID*.
- **Non-Goals**: 
  - Membangun *engine form builder* baru (sepenuhnya mengandalkan ekosistem SurveyJS).

## 3. Technical Specifications

- **Architecture Overview**:
  - **Backend Engine**: Laravel 13.x, PHP 8.x
  - **Admin Dashboard**: Filament v5 (mencakup CRUD Kategori, CRUD Survei, dan Dasbor).
  - **Database Pattern**: 
    - Tabel relasional standar untuk Kategori dan Kuesioner.
    - Penggunaan tipe kolom `JSONB` atau `JSON` untuk menyimpan *Schema Survei* dan *Submission / Answers* untuk skalabilitas dinamis.
  - **Frontend / Form Builder**: 
    - **Survey Builder**: *SurveyJS Creator* (akan di- *embed* / *wrap* ke dalam *custom widget* atau *Livewire component* di ruang Admin).
    - **Survey Runner**: *SurveyJS Form Engine* diimplementasikan untuk responden (teknologi bisa menggunakan Vue.js, Blade, atau murni JS bergantung strategi integrasi).
- **Integration Points**:
  - Validasi *Input*: JSON jawaban (*submission*) harus divalidasi keutuhannya terhadap Schema JSON asli di *backend*.
- **Security & Privacy**:
  - (TBD) Perizinan akses: Hak akses *role-based* untuk melihat hasil survei tertentu.
  - Pencegahan modifikasi *payload* respon secara sewenang-wenang.

## 4. Risks & Roadmap

### Phased Rollout (Usulan Roadmap)
- **Phase 1 (MVP - Foundation)**: Integrasi struktur *database* Kategori, penerapan *SurveyJS Creator* ke dalam Filament Admin, dan publikasi *SurveyJS Runner* bagi responden. Perekaman *raw submission* menggunakan kolom JSON.
- **Phase 2 (Analytics & Dashboard)**: Implementasi pengolahan hasil survei, agregasi data via *SQL JSON functions* (atau proses asinkron ke tabel turunan), dan penayangan visualisasi/dasbor per kategori/kuesioner spesifik.
- **Phase 3 (Optimization)**: Logika lanjutan untuk pengiriman notifikasi, optimasi *query reporting*, serta pengkayaan *plugin* UX di Panel Filament.

### Technical Risks
1. **Livewire & SurveyJS Integration**: *SurveyJS Creator* sangat bergantung pada manajemen DOM *client-side* murni (umumnya React/Knockout/Vue). Menggabungkannya dengan arsitektur Livewire (basis Filament) yang asinkron mungkin rentan terjadi konflik sinkronisasi *state*. **Mitigasi**: Menggunakan Alpine.js wrapper berkemampuan `wire:ignore`.
2. **Lisensi SurveyJS**: Produk inti dari *SurveyJS Creator* mensyaratkan lisensi komersial untuk lingkungan produksi. Perlu diperhatikan batasan *budget* lisensi di awal perencanaan.
3. **Complex Analytics on JSON**: *Querying* pada data berstruktur JSON pada relasional DB dapat mengalami degradasi di volume raksasa. **Mitigasi**: Merancang *Event/Job* Laravel untuk mengekstrak data dari JSON ke tabel matriks saat *submit*.

---
> [!IMPORTANT]
> **TBD (To Be Determined) - Klarifikasi yang dibutuhkan dari Anda:**
> 1. Apakah panel *Respondent* mau disatukan dengan *ecosystem* aplikasi Vue.js (semisal Inertia.js) atau sepenuhnya dirender oleh standard Laravel Blade/Livewire?
> 2. Bagaimana tingkat kompleksitas formula untuk analitis dan Dasbor tiap spesifik kuesionernya?
> 3. Apakah Anda sadar dan/atau tidak mempermasalahkan isu *Commercial License* yang wajib dimiliki untuk penggunaan **SurveyJS Creator** di *production*?
