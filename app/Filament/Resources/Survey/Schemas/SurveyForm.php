<?php

namespace App\Filament\Resources\Survey\Schemas;

use App\Enums\SurveyMode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class SurveyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Umum')
                    ->schema([
                        Select::make('kategori_id')
                            ->label('Kategori')
                            ->relationship('kategori', 'name')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->columnSpan(1),
                        Select::make('mode')
                            ->options(SurveyMode::class)
                            ->required()
                            ->default(SurveyMode::Single)
                            ->columnSpan(1),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi singkat tentang survei ini...')
                            ->rows(3)
                            ->columnSpanFull(),
                        DateTimePicker::make('starts_at')
                            ->label('Mulai Pada')
                            ->columnSpan(1),
                        DateTimePicker::make('ends_at')
                            ->label('Berakhir Pada')
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->columnSpan(1),
                        Toggle::make('is_quiz')
                            ->label('Mode Kuis (Uji Kompetensi)')
                            ->helperText('Jika aktif, sistem akan menghitung skor berdasarkan correctAnswer di schema.')
                            ->default(false)
                            ->live()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Pengaturan Kuis')
                    ->schema([
                        Group::make()->statePath('settings')->schema([
                            TextInput::make('passing_score')
                                ->label('Nilai Standar Kelulusan (Passing Score)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->helperText('Skor minimal untuk lulus (0-100)'),
                            Toggle::make('allow_retake')
                                ->label('Boleh Mengulang Kuis?')
                                ->live()
                                ->helperText('Jika aktif, peserta yang nilainya di bawah standar kelulusan diizinkan untuk mencoba lagi.'),
                            TextInput::make('max_retakes')
                                ->label('Maksimal Percobaan')
                                ->numeric()
                                ->minValue(1)
                                ->visible(fn(Get $get) => (bool) $get('allow_retake'))
                                ->required(fn(Get $get) => (bool) $get('allow_retake'))
                                ->helperText('Berapa kali peserta diizinkan mengulang? (Contoh: 2 berarti total percobaan bisa 2 kali)'),
                        ])->columns(2),
                    ])
                    ->visible(fn(Get $get) => (bool) $get('is_quiz'))
                    ->columns(1),

                Section::make('Akses Kontrol')
                    ->schema([
                        Select::make('access_level')
                            ->label('Level Akses')
                            ->options([
                                'public' => 'Umum (Tanpa Login)',
                                'auth' => 'Harus Login',
                                'role' => 'Role Spesifik',
                            ])
                            ->default('public')
                            ->required()
                            ->live()
                            ->columnSpan(1),
                        Select::make('allowed_roles')
                            ->label('Role yang Diizinkan')
                            ->options(fn() => Role::pluck('name', 'name')->toArray())
                            ->multiple()
                            ->visible(fn(Get $get) => $get('access_level') === 'role')
                            ->required(fn(Get $get) => $get('access_level') === 'role')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

            ]);
    }
}
