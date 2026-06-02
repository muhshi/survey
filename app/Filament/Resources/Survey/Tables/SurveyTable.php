<?php

namespace App\Filament\Resources\Survey\Tables;

use App\Filament\Resources\Survey\SurveyResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SurveyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('jawabanRespondens')->with(['groups']))
            ->columns([
                TextColumn::make('kategori.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->sortable()
                    ->searchable()
                    ->limit(40),
                TextColumn::make('public_url')
                    ->label('Link Survei')
                    ->state(fn ($record) => $record->getPublicUrl())
                    ->copyable()
                    ->copyableState(fn ($record) => $record->getPublicUrl())
                    ->copyMessage('Link disalin!')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->extraAttributes([
                        'style' => 'max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;',
                    ]),
                TextColumn::make('mode')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('access_level')
                    ->label('Akses')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'public' => 'Umum',
                        'auth' => 'Login',
                        'role' => 'Role',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'auth' => 'warning',
                        'role' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('jawaban_respondens_count')
                    ->label('Jawaban')
                    ->getStateUsing(function ($record) {
                        $groups = $record->groups;

                        if ($groups->isEmpty()) {
                            return $record->jawaban_respondens_count;
                        }

                        $groupIds = $groups->pluck('id')->all();

                        $totalGroupUsers = DB::table('group_user')
                            ->whereIn('group_id', $groupIds)
                            ->distinct('user_id')
                            ->count('user_id');

                        if ($totalGroupUsers === 0) {
                            return $record->jawaban_respondens_count;
                        }

                        $submittedGroupUsers = $record->jawabanRespondens()
                            ->whereIn('user_id', function ($query) use ($groupIds) {
                                $query->select('user_id')
                                    ->from('group_user')
                                    ->whereIn('group_id', $groupIds);
                            })
                            ->distinct('user_id')
                            ->count('user_id');

                        return "{$submittedGroupUsers} / {$totalGroupUsers}";
                    })
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('starts_at')
                    ->label('Berlaku Dari')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label('Berlaku Sampai')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                SelectFilter::make('access_level')
                    ->label('Level Akses')
                    ->options([
                        'public' => 'Umum',
                        'auth' => 'Login',
                        'role' => 'Role',
                    ]),
            ])
            ->recordActions([
                Action::make('design')
                    ->label('Desain')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->url(fn ($record) => SurveyResource::getUrl('design', ['record' => $record])),
                Action::make('viewSubmissions')
                    ->label('Jawaban')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => SurveyResource::getUrl('submissions', ['record' => $record])),
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplikat')
                    ->excludeAttributes(['slug', 'created_at', 'updated_at'])
                    ->form([
                        TextInput::make('title')
                            ->label('Judul Survei/Kuis Baru')
                            ->required(),
                        Select::make('kategori_id')
                            ->label('Kategori')
                            ->relationship('kategori', 'name')
                            ->required(),
                        Toggle::make('is_quiz')
                            ->label('Jadikan Mode Kuis?'),
                    ])
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['title'] = $data['title'].' (Copy)';

                        return $data;
                    })
                    ->successRedirectUrl(fn (Model $replica): string => SurveyResource::getUrl('edit', ['record' => $replica])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
