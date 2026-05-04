<?php

namespace App\Filament\Resources\Survey\Pages;

use App\Filament\Resources\Survey\SurveyResource;
use App\Models\JawabanResponden;
use App\Models\Survey;
use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewSubmissions extends Page implements HasTable, HasSchemas
{
    use InteractsWithTable;
    use InteractsWithSchemas;

    protected static string $resource = SurveyResource::class;

    protected string $view = 'filament.resources.survey.pages.view-submissions';

    protected static ?string $title = 'Jawaban Responden';

    public Survey $record;

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->components([
                Section::make('Ringkasan Survei')
                    ->description('Informasi utama mengenai status pengisian survei.')
                    ->schema([
                        TextEntry::make('jawaban_respondens_count')
                            ->label('Total Jawaban')
                            ->state(fn () => $this->record->jawabanRespondens()->count())
                            ->weight(FontWeight::Bold)
                            ->color(Color::Sky)
                            ->icon('heroicon-m-chat-bubble-bottom-center-text'),
                            
                        TextEntry::make('mode')
                            ->label('Mode Sistem')
                            ->badge()
                            ->color(Color::Sky),

                        TextEntry::make('public_url')
                            ->label('Link Akses')
                            ->state(fn () => 'Buka Link Survei')
                            ->action(
                                Action::make('open_link')
                                    ->url(fn () => $this->record->getPublicUrl())
                                    ->openUrlInNewTab()
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->color(Color::Sky)
                            )
                            ->icon('heroicon-m-link'),
                    ])
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(JawabanResponden::query()->where('survey_id', $this->record->id))
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Responden')
                    ->default('Anonim')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('submitted_at')
                    ->label('Waktu Submit')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('metadata.ip')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->color('gray'),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->actions([
                ViewAction::make()
                    ->label('Lihat Jawaban')
                    ->modalHeading('Detail Jawaban Lengkap')
                    ->infolist([
                        Section::make('Data Responden')
                            ->schema([
                                TextEntry::make('user.name')->label('Nama')->default('Anonim'),
                                TextEntry::make('submitted_at')->label('Waktu Submit')->dateTime('d M Y H:i:s'),
                                TextEntry::make('metadata.ip')->label('IP Metadata'),
                            ])->columns(3),
                        Section::make('Isi Kuesioner')
                            ->schema([
                                KeyValueEntry::make('payload')
                                    ->label('Jawaban yang Dikirimkan')
                                    ->keyLabel('Pertanyaan / Field')
                                    ->valueLabel('Jawaban'),
                            ]),
                    ]),
            ]);
    }
}
