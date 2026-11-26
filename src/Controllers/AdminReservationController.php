<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ReservationService;
use Velo\Http\Responses\Concrete\ViewResponse;

final readonly class AdminReservationController
{
    public function __construct(private ReservationService $reservationService)
    {
    }

    public function __invoke(): ViewResponse
    {
        return new ViewResponse('admin_reservations.php', [
            'reservations' => $this->reservationService->getAllReservations(),
        ]);
    }
}
