<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Flight;
use App\Entity\Seat;
use App\Entity\User;
use App\Form\BookingFormType;
use App\Form\EditAccountFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user/details/{userId}", name="account_details")
     */
    public function userDetails($userId){
        $r = $this->getDoctrine()->getRepository(User::class);
        $user = $r->findOneBy(['id' => $userId]);

        return $this->render('user/accountDetails.html.twig', [
            'user' => $user
        ]);
    }
    /**
     * @Route("/user/edit/{userId}", name="edit_account")
     */
    public function editAccount($userId, Request $request) {
        $r = $this->getDoctrine()->getRepository(User::class);
        $user = $r->findOneBy(['id' => $userId]);
        $form = $this->createForm(EditAccountFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success','Account successfully edited');
            return $this->redirectToRoute('account_details', ['userId' => $userId]);
        }
        return $this->render('user/form.html.twig',[
            'object' => 'account',
            'action' => 'Edit',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/flight/booking/{flightId}", name="make_booking")
     */
    public function bookFlight($flightId, Request $request) {
        $booking = new Booking();

        $r= $this->getDoctrine()->getRepository(Flight::class);
        $flight = $r->findOneBy(['id' => $flightId]);
        $session = $request->getSession();

        $form = $this->createForm(BookingFormType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $booking->setCode(uniqid());
            $booking->setStatus('ongoing');
            $booking->setUser($this->getUser());

            if ($session->has('hasLooped') && $session->get('hasLooped') == true) {
                $booking->setFlight($session->get('beginFlight'));
                $booking->setReturnFlight($flight);
                $session->clear();
            }
            else {
                $booking->setFlight($flight);
            }

            $em->persist($booking);
            $em->flush();

            return $this->redirectToRoute('pick_seat',['bookingId' => $booking->getId(), 'flightId' => $flightId]);
        }

        return $this->render('user/form.html.twig',[
            'object' => 'booking',
            'action' => 'Create',
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route ("/user/flight/book/pick/seat/{bookingId}/{flightId}", name="pick_seat")
     */
    public function pickSeat($bookingId,$flightId) {
        $r = $this->getDoctrine()->getRepository(Flight::class);
        $re = $this->getDoctrine()->getRepository(Booking::class);
        $booking = $re->findOneBy(['id' => $bookingId]);
        $flight = $r->findOneBy(['id' => $flightId]);

        $seats = $flight->getSeats();
        return $this->render('user/pickSeat.html.twig',[
            'seats' => $seats,
            'booking' => $booking,
        ]);
    }
    /**
     * @Route ("/user/process/seat/booking/{seatId}/{bookingId}", name="processBooking")
     */
    public function processBooking($seatId, $bookingId, Request $request) {
        $r= $this->getDoctrine()->getRepository(Seat::class);
        $re = $this->getDoctrine()->getRepository(Booking::class);
        $session = $request->getSession();

        $booking = $re->findOneBy(['id' => $bookingId]);
        $seat = $r->findOneBy(['id' => $seatId]);

        $em = $this->getDoctrine()->getManager();

        $seat->setBooking($booking);
        $em->persist($seat);
        $em->flush();

        if ($session->has('returnFlights')) {
            $session->set('hasLooped', true);
            $session->set('beginFlight', $booking->getFlight());

            return $this->render('searchFlightResults.html.twig',[
                'flights' => $session->get('returnFlights')
            ]);
        }

        return $this->redirectToRoute('user_bookings');
    }

    /**
     * @Route("/user/bookings", name="user_bookings")
     */
    public function userBookings(Request $request) {
        $r = $this->getDoctrine()->getRepository(Booking::class);
        $bookings = $r->findBy(['user' => $this->getUser()]);
        foreach ($bookings as $booking) {
            if($booking->getSeats()[0] == null) {
                $em= $this->getDoctrine()->getManager();
                $booking->setStatus('cancelled');

                if ($booking->getFlightMessage() == 'User has cancelled their booking') {
                    null;
                }
                else {
                    $booking->setFlightMessage('Seat was not chosen');
                }

                $em->persist($booking);
                $em->flush();
            }
        }
        return $this->render('user/userBookings.html.twig',[
            'bookings' => $bookings
        ]);
    }

    /**
     * @Route("/user/bookings/cancel/{bookingId}", name="cancel_booking")
     */
    public function cancelBooking($bookingId) {
        $em = $this->getDoctrine()->getManager();

        $r = $this->getDoctrine()->getRepository(Booking::class);
        $booking = $r->findOneBy(['id' => $bookingId]);

        $booking->setStatus('cancelled');
        $booking->setFlightMessage('User has cancelled their booking');

        foreach ($booking->getSeats() as $seat) {
            if ($seat != null) {
                $booking->removeSeat($seat);
            }
        }

        $em->persist($booking);
        $em->flush();
        return $this->redirectToRoute('user_bookings');
    }

    /**
     * @Route("/user/bookings/delete/{bookingId}", name="delete_booking")
     */
    public function deleteBooking($bookingId) {
        $r = $this->getDoctrine()->getRepository(Booking::class);
        $booking = $r->findOneBy(['id' => $bookingId]);
        $em = $this->getDoctrine()->getManager();

        if ($booking->getSeats()[0] != null) {
            $booking->removeSeat($booking->getSeats()[0]);
            $em->persist($booking);
            $em->flush();
        }
        $em->remove($booking);
        $em->flush();
        return $this->redirectToRoute('user_bookings');
    }

    /**
     * @Route("/flight/compare/", name="compare")
     */
    public function compare(RequestStack $request)
    {
        $session = new Session();
        $session->clear();
        $em = $this->getDoctrine()->getManager();

        $query = $request->getCurrentRequest()->query;
        $beginAirport = $query->get('beginAirport');
        $destination = $query->get('destination');
        $date = $query->get('date');
        $returnDate = $query->get('returnDate');
        $trip = $query->get('trip');

        if($trip == "oneWay") {
            // 1 vlucht
            $flights = $em->getRepository(Flight::class)->findBy(["beginAirport"=>$beginAirport,"destination"=>$destination,"date"=> new \DateTime($date),'status' => 'active']);
            return $this->render('searchFlightResults.html.twig',['flights' => $flights]);
        }
        else {
            // 2 vluchten
            $beginFlights = $em->getRepository(Flight::class)->findBy(["beginAirport"=>$beginAirport,"destination"=>$destination,"date"=> new \DateTime($date),'status' => 'active']);
            $returnFlights = $em->getRepository(Flight::class)->findBy(["beginAirport"=>$destination,"destination"=>$beginAirport,"date"=> new \DateTime($returnDate),'status' => 'active']);

            $session->set('returnFlights', $returnFlights);
            $session->set('hasLooped', false);
            return $this->render('searchFlightResults.html.twig',['flights' => $beginFlights]);
        }
    }
}
