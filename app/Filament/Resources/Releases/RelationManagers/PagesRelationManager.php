<?php

namespace App\Filament\Resources\Releases\RelationManagers;

use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Models\Page;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PagesRelationManager extends RelationManager
{
    protected static string $relationship = 'pages';

    protected static ?string $recordTitleAttribute = 'title';

    public function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('position');
    }

    public function form(Schema $schema): Schema
    {
        return PageForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('page_type')
                    ->badge(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Order')
                    ->sortable()
                    ->visible(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(fn (): string => route('filament.admin.resources.pages.create', [
                        'release_id' => $this->getOwnerRecord()->id
                    ])),
            ])
            ->actions([
                Action::make('move_up')
                    ->label('↑')
                    ->icon('heroicon-o-arrow-up')
                    ->color('gray')
                    ->action(function (Page $record) {
                        $this->movePageUp($record);
                    })
                    ->visible(fn (Page $record) => $this->canMoveUp($record)),
                
                Action::make('move_down')
                    ->label('↓')
                    ->icon('heroicon-o-arrow-down')
                    ->color('gray')
                    ->action(function (Page $record) {
                        $this->movePageDown($record);
                    })
                    ->visible(fn (Page $record) => $this->canMoveDown($record)),
                
                EditAction::make()
                    ->url(fn (Page $record): string => route('filament.admin.resources.pages.edit', ['record' => $record])),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-set position for new pages within this release
        if (empty($data['position'] ?? null)) {
            $last = Page::where('release_id', $data['release_id'])->max('position') ?? 0;
            $data['position'] = $last + 1;
        }

        return $data;
    }

    public function canMoveUp(Page $record): bool
    {
        return $record->position > 1;
    }

    public function canMoveDown(Page $record): bool
    {
        $maxPosition = Page::where('release_id', $record->release_id)->max('position');
        return $record->position < $maxPosition;
    }

    public function movePageUp(Page $record): void
    {
        $currentPosition = $record->position;
        $targetPosition = $currentPosition - 1;

        // Find the page at the target position
        $targetPage = Page::where('release_id', $record->release_id)
            ->where('position', $targetPosition)
            ->first();

        if ($targetPage) {
            // Use temporary position to avoid unique constraint violation
            $tempPosition = 999999; // Use a high number that won't conflict
            
            // Step 1: Move current record to temporary position
            $record->update(['position' => $tempPosition]);
            
            // Step 2: Move target record to current position
            $targetPage->update(['position' => $currentPosition]);
            
            // Step 3: Move current record to target position
            $record->update(['position' => $targetPosition]);
        }
    }

    public function movePageDown(Page $record): void
    {
        $currentPosition = $record->position;
        $targetPosition = $currentPosition + 1;

        // Find the page at the target position
        $targetPage = Page::where('release_id', $record->release_id)
            ->where('position', $targetPosition)
            ->first();

        if ($targetPage) {
            // Use temporary position to avoid unique constraint violation
            $tempPosition = 999999; // Use a high number that won't conflict
            
            // Step 1: Move current record to temporary position
            $record->update(['position' => $tempPosition]);
            
            // Step 2: Move target record to current position
            $targetPage->update(['position' => $currentPosition]);
            
            // Step 3: Move current record to target position
            $record->update(['position' => $targetPosition]);
        }
    }

}
