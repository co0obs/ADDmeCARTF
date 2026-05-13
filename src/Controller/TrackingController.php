<?php

namespace App\Controller;

use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TrackingController extends AbstractController
{

    #[Route('/tracking/{id}', name: 'app_order_tracking')]
    public function index(Order $order): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot track an order that does not belong to you.');
        }

        return $this->render('order/tracking.html.twig', [
            'order' => $order, 
            'trackingNumber' => $order->getTrackingNumber() ?? 'JNT-88492011PH',
            'eta' => 'Today, 4:30 PM'
        ]);
    }

    #[Route('/api/mock-logistics-tracking', name: 'api_mock_tracking', methods: ['GET'])]
    public function mockTrackingApi(): JsonResponse
    {
        // Simulating the coordinates of a delivery rider currently in transit
        return new JsonResponse([
            'status' => 'success',
            'courier' => 'J&T Express',
            'current_status' => 'Out for Delivery',
            'driver_name' => 'Juan Dela Cruz',
            'current_location' => [
                'lat' => 10.3157, // Latitude (Cebu City area)
                'lng' => 123.8854 // Longitude
            ],
            'last_update' => date('h:i A')
        ]);
    }
}