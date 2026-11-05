<?php

namespace App\Filament\Actions;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Paymob\PaymobRefundException;
use App\Services\Paymob\RefundPaymobPayment;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class RefundPaymentAction
{
    public static function make(): Action
    {
        return Action::make('refund')
            ->label(__('filament/resources.payments.refund.action'))
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('filament/resources.payments.refund.heading'))
            ->modalDescription(__('filament/resources.payments.refund.description'))
            ->modalSubmitActionLabel(__('filament/resources.payments.refund.action'))
            ->authorize(function (Model $record): bool {
                $payment = self::payment($record);
                $user = auth()->user();

                return $payment instanceof Payment
                    && $user instanceof User
                    && $user->can('update', $payment);
            })
            ->visible(fn (Model $record): bool => self::payment($record)?->isRefundable() === true)
            ->action(function (Model $record): void {
                $payment = self::payment($record);

                if (! $payment instanceof Payment) {
                    return;
                }

                try {
                    app(RefundPaymobPayment::class)->refund($payment);
                } catch (PaymobRefundException $exception) {
                    Notification::make()
                        ->danger()
                        ->title(__('filament/resources.payments.refund.failed'))
                        ->body($exception->getMessage())
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title(__('filament/resources.payments.refund.success'))
                    ->send();
            });
    }

    private static function payment(Model $record): ?Payment
    {
        if ($record instanceof Payment) {
            return $record;
        }

        if ($record instanceof Order) {
            return $record->refundablePayment();
        }

        return null;
    }
}
