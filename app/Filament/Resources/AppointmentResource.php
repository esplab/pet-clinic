<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\DatePicker::make('date')
                        ->required()
                        ->native(false),
                    Forms\Components\TimePicker::make('start')
                        ->required()
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->minutesStep(10)
                        ->native(false),
                    Forms\Components\TimePicker::make('end')
                        ->required()
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->minutesStep(10)
                        ->native(false),
                    Forms\Components\Select::make('pet_id')
                        ->required()
                        ->relationship('pet', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),
                    Forms\Components\TextInput::make('description')
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options(AppointmentStatus::class)
                        ->native(false)
                        ->visibleOn(Pages\EditAppointment::class)
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('pet.avatar')->circular()->label('Avatar'),
                Tables\Columns\TextColumn::make('pet.name')->sortable()->searchable()->label('Name'),
                Tables\Columns\TextColumn::make('date')->date('d M Y')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('start')->sortable(),
                Tables\Columns\TextColumn::make('end')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
            ])
            ->filters([
                //

            ])
            ->actions([
                Tables\Actions\Action::make('Confirm')
                    ->action(function (Appointment $record) {
                        $record->status = AppointmentStatus::Confirmed;
                        $record->save();
                    })
                    ->visible(fn (Appointment $record) => ($record->status == AppointmentStatus::Created || $record->status == AppointmentStatus::Canceled))
                    ->color('success')
                    ->icon('heroicon-o-check'),
                Tables\Actions\Action::make('Cancel')
                    ->action(function (Appointment $record) {
                        $record->status = AppointmentStatus::Canceled;
                        $record->save();
                    })
                    ->visible(fn (Appointment $record) => ($record->status != AppointmentStatus::Canceled))
                    ->color('danger')
                    ->icon('heroicon-o-x-mark'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
