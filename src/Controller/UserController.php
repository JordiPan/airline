<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Flight;
use App\Entity\Group;
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
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(BookingFormType::class, $booking);
        $form->handleRequest($request);

        if ($session->has('hasLooped') && $session->get('hasLooped') == true && !$session->has('group')) {
            $re = $this->getDoctrine()->getRepository(Booking::class);
            $booking = $re->findOneBy(['id' => $session->get('beginBooking')->getId()]);
            $booking->setReturnFlight($flight);
            $em->persist($booking);
            $em->flush();
            $session->clear();

            return $this->redirectToRoute('pick_seat',['bookingId' => $booking->getId(), 'flightId' => $flightId, 'group' => 0]);
        }

        else {
            if ($form->isSubmitted() && $form->isValid()) {
                //If there is a group it will make bookings for every user in the group
                if ($session->has('groupId')) {
                    $rep = $this->getDoctrine()->getRepository(Group::class);
                    $group = $rep->findOneBy(['id' => $session->get('groupId')]);

                    $array = [];
                    foreach ($group->getGroupCustomers() as $user) {
                        $groupBooking = new Booking();
                        $groupBooking->setCode(uniqid());
                        $groupBooking->setStatus('ongoing');
                        $groupBooking->setUser($user->getUser());
                        $groupBooking->setFlight($flight);
                        $groupBooking->setClass($booking->getClass());
                        $em->persist($groupBooking);

                        $em->flush();
                        array_push($array, $groupBooking->getId());
                        $session->set('bookingIds', $array);
                    }
                    return $this->redirectToRoute('pick_seat',['bookingId' => $groupBooking->getId(), 'flightId' => $flightId, 'group' => 1]);
                }
                else {
                    $booking->setCode(uniqid());
                    $booking->setStatus('ongoing');
                    $booking->setUser($this->getUser());
                    $booking->setFlight($flight);;
                    $em->persist($booking);
                    $em->flush();
                    return $this->redirectToRoute('pick_seat',['bookingId' => $booking->getId(), 'flightId' => $flightId, 'group' => 0]);
                }
            }
        }

        return $this->render('user/form.html.twig',[
            'object' => 'booking',
            'action' => 'Create',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/user/flight/book/pick/seat/{bookingId}/{flightId}/{group}", name="pick_seat")
     */
    public function pickSeat($bookingId,$flightId,$group, Request $request) {
        $r = $this->getDoctrine()->getRepository(Flight::class);
        $re = $this->getDoctrine()->getRepository(Booking::class);
        $booking = $re->findOneBy(['id' => $bookingId]);
        $flight = $r->findOneBy(['id' => $flightId]);
        $seats = $flight->getSeats();
        return $this->render('user/pickSeat.html.twig',[
            'seats' => $seats,
            'booking' => $booking,
            'group' => $group
        ]);
    }
    //for the lonely people
    /**
     * @Route ("/user/process/seat/booking/", name="processBooking")
     */
    public function processBooking(Request $request,RequestStack $stack) {
        $r = $this->getDoctrine()->getRepository(Seat::class);
        $re = $this->getDoctrine()->getRepository(Booking::class);

        $em = $this->getDoctrine()->getManager();

        $query = $stack->getCurrentRequest()->query;
        $seatId= $query->get('seatId');
        $bookingId = $query->get('bookingId');

        $session = $request->getSession();
        $booking = $re->findOneBy(['id' => $bookingId]);
        $seat = $r->findOneBy(['id' => $seatId]);

        $seat->setBooking($booking);
        $em->persist($seat);
        $em->flush();

        if ($session->has('returnFlights')) {
            $session->set('hasLooped', true);
            $session->set('beginBooking', $booking);

            return $this->render('searchFlightResults.html.twig', [
                'flights' => $session->get('returnFlights')
            ]);
        }
        else {
            return $this->redirectToRoute('user_bookings');
        }
    }

    //This is the route for a group booking
    /**
     * @Route ("/user/process/seat/group/", name="processGroupBooking")
     */
    public function processGroupBooking(Request $request,RequestStack $stack) {
        $session = $request->getSession();

        $query = $stack->getCurrentRequest()->query;
        $seatId= $query->get('seatId');

        $re = $this->getDoctrine()->getRepository(Seat::class);
        $rep = $this->getDoctrine()->getRepository(Booking::class);
        $em= $this->getDoctrine()->getManager();
        $seat = $re->findOneBy(['id' => $seatId]);

            for ($i = 1; $i < count($session->get('bookingIds')); $i++) {

                $bookingId = $session->get('bookingIds')[$i];
                $booking = $rep->findOneBy(['id' => $bookingId]);
                $seat->setBooking($booking);

                $em->persist($seat);
                $em->flush();

                $array = $session->get('bookingIds');

                unset($array[$i]);
                $goodIndex = array_values($array);

                $session->set('bookingIds',$goodIndex);
                return $this->redirectToRoute('pick_seat',['flightId' => $booking->getFlight()->getId(),'bookingId' => $bookingId,'group' => 1]);
            }
//        return $this->render('user/userBookings.html.twig');
        return $this->redirectToRoute("homepage");
    }

    /**
     * @Route("/user/bookings", name="user_bookings")
     */
    public function userBookings(Request $request) {
        $r = $this->getDoctrine()->getRepository(Booking::class);
        $bookings = $r->findBy(['user' => $this->getUser()]);
        $em = $this->getDoctrine()->getManager();
        foreach ($bookings as $booking) {
            foreach ($booking->getSeats() as $seat) {

                if ($seat == null) {

                    $booking->setStatus('cancelled');

                    if ($booking->getFlightMessage() == 'User has cancelled their booking') {
                        null;
                    }
                    else {
                        $booking->setFlightMessage('Seat was not chosen');
                    }
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
    public function deleteBooking($bookingId){
        $r = $this->getDoctrine()->getRepository(Booking::class);
        $booking = $r->findOneBy(['id' => $bookingId]);
        $em = $this->getDoctrine()->getManager();

        foreach ($booking->getSeats() as $seat) {
            if ($seat != null) {
                $booking->removeSeat($seat);
                $em->persist($booking);
                $em->flush();
            }
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
            $session->remove('returnFlights');
            $session->set('hasLooped', false);
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
