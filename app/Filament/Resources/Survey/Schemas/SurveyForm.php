<?php

namespace App\Filament\Resources\Survey\Schemas;

use App\Enums\SurveyMode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
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
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->columnSpan(1),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
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
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->columnSpan(1),
                        DateTimePicker::make('starts_at')
                            ->label('Mulai Pada')
                            ->columnSpan(1),
                        DateTimePicker::make('ends_at')
                            ->label('Berakhir Pada')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

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
                            ->options(fn () => Role::pluck('name', 'name')->toArray())
                            ->multiple()
                            ->visible(fn (Get $get) => $get('access_level') === 'role')
                            ->required(fn (Get $get) => $get('access_level') === 'role')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

            ]);
    }
}
