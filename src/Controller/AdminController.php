<?php

namespace App\Controller;

use App\Entity\Airport;
use App\Entity\Booking;
use App\Entity\Flight;
use App\Entity\Seat;
use App\Form\EditFlightFormType;
use App\Form\FlightFormType;
use App\Repository\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route ("/admin", name="admin_homepage")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    /**
     * @Route ("/admin/flights", name="admin_flights")
     */
    public function adminflights(): Response
    {
        $r = $this->getDoctrine()->getRepository(Flight::class);
        $flights = $r->findAll();

        return $this->render('admin/adminFlights.html.twig', [
            'flights' => $flights
        ]);
    }
    /**
     * @Route ("/admin/flights/add", name="add_flight")
     */
    public function addFlight(Request $request): Response
    {
        $flight = new Flight();
        $form = $this->createForm(FlightFormType::class, $flight);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //making the seats for the flight
            for ($i = 0; $i < $flight->getAirplane()->getSeats(); $i++) {
                $seat = new Seat();
                $seat->setSeat($i);
                $seat->setFlight($flight);
                $em->persist($seat);
            }

            $flight->setStatus('active');
            $em->persist($flight);
            $em->flush();
            return $this->redirectToRoute('admin_flights');
        }
        return $this->render('admin/createFlightForm.html.twig',['form'=> $form->createView()]);
    }
    /**
     * @Route ("/admin/airports", name="admin_airports")
     */
    public function adminAirport() {
        $r = $this->getDoctrine()->getRepository(Airport::class);
        $airports = $r->findAll();
        return $this->render('admin/airports.html.twig',[
            'airports' => $airports
        ]);
    }
    /**
     * @Route("/admin/change/status/{airportId}", name="status_change")
     */
    public function statusChange($airportId)
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        $r = $doctrine->getRepository(Airport::class);
        $airport = $r->findOneBy(['id' => $airportId]);

        $re = $doctrine->getRepository(Flight::class);
        $flights = $re->findAll();

        $rep = $doctrine->getRepository(Booking::class);
        $bookings = $rep->findAll();


        if ($airport->getStatus() == 'active') {
            $airport->setStatus('inactive');

                foreach ($flights as $flight) {
                    if ($flight->getBeginAirport() == $airport || $flight->getDestination() == $airport) {
                        $flight->setStatus('inactive');
                    }
                }
        }
        else {
            $airport->setStatus('active');

            foreach ($flights as $flight) {
                if ($flight->getBeginAirport() == $airport || $flight->getDestination() == $airport) {
                    $flight->setStatus('active');
                }
            }
        }
        //cancels the bookings that are connected to the inactive airport
        foreach ($bookings as $booking) {
            if ($booking->getFlight()->getStatus() == 'inactive' && $booking->getStatus() != 'cancelled') {
                $em = $this->getDoctrine()->getManager();

                $booking->setStatus('cancelled');
                $booking->removeSeat($booking->getSeats()[0]);

                $em->persist($booking);
            }
        }

        $em->persist($airport);
        $em->flush();

        $this->addFlash('success', 'status changed!');
        return $this->redirectToRoute('admin_airports');
    }
    /**
     * @Route ("/admin/edit/flight/{flightId}", name="edit_flight")
     */
    public function editFlight($flightId, Request $request) {
        $doctrine = $this->getDoctrine();
        $r = $doctrine->getRepository(Flight::class);
        $flight = $r->findOneBy(['id' => $flightId]);

        $form = $this->createForm(EditFlightFormType::class, $flight);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $re = $doctrine->getRepository(Booking::class);
            $bookings = $re->findAll();

            foreach ($bookings as $booking) {
                if ($booking->getFlight() == $flight && $booking->getStatus() != 'cancelled') {
                    
                    if ($booking->getFlight()->getDate() != $flight->getDate()) {
                        $booking->setFlightMessage('flight date/time changed');
                    }

                    $em->persist($booking);
                }
            }

            $em->persist($flight);
            $em->flush();
            return $this->redirectToRoute('admin_flights');
        }
        return $this->render('admin/editFlightForm.html.twig',[
            'form' => $form->createView(),
            'action' => 'Edit',
            'object' => 'flight'
        ]);
    }
}
