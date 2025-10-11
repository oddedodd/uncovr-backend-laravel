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
use Illuminate\Support\Facades\DB;

class PagesRelationManager extends RelationManager
{
    protected static string $relationship = 'pages';

    protected static ?string $recordTitleAttribute = 'title';

    // Cache for position data to avoid multiple queries
    private ?array $positionCache = null;

    public function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('position')
            ->select(['id', 'title', 'slug', 'page_type', 'position', 'status', 'updated_at', 'release_id']); // Only select needed columns
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
            ->deferLoading() // Defer loading until table is visible
            ->poll('30s') // Auto-refresh every 30 seconds
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
        $positionData = $this->getPositionData();
        return $record->position < $positionData['max'];
    }

    private function getPositionData(): array
    {
        if ($this->positionCache === null) {
            $releaseId = $this->getOwnerRecord()->id;
            $pages = Page::where('release_id', $releaseId)
                ->select(['position'])
                ->orderBy('position')
                ->get();
            
            $this->positionCache = [
                'min' => $pages->min('position') ?? 0,
                'max' => $pages->max('position') ?? 0,
                'positions' => $pages->pluck('position')->toArray(),
            ];
        }
        
        return $this->positionCache;
    }

    public function movePageUp(Page $record): void
    {
        $this->movePage($record, -1);
    }

    public function movePageDown(Page $record): void
    {
        $this->movePage($record, 1);
    }

    private function movePage(Page $record, int $direction): void
    {
        $currentPosition = $record->position;
        $targetPosition = $currentPosition + $direction;
        
        // Use database transaction for atomicity
        DB::transaction(function () use ($record, $currentPosition, $targetPosition) {
            // Get a safe temporary position that won't conflict
            $tempPosition = Page::where('release_id', $record->release_id)
                ->max('position') + 1000; // Use a high number that won't conflict
            
            // Step 1: Move current record to temporary position
            Page::where('id', $record->id)->update(['position' => $tempPosition]);
            
            // Step 2: Move target record to current position
            Page::where('release_id', $record->release_id)
                ->where('position', $targetPosition)
                ->update(['position' => $currentPosition]);
            
            // Step 3: Move current record to target position
            Page::where('id', $record->id)->update(['position' => $targetPosition]);
        });
        
        // Clear cache after move operation
        $this->positionCache = null;
    }

}
