<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Flight;
use App\Entity\User;
use App\Form\BookingFormType;
use App\Form\SearchBookingFormType;
use App\Form\SearchFlightFormType;
use App\Form\UserFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class VisitorController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request)
    {
        $form = $this->createForm(SearchFlightFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['returnDate'] == null) {
                $returnDate = $data['returnDate'];
            }
            else {
                $returnDate = $data['returnDate']->format('Y-m-j');
            }
            return $this->redirectToRoute('compare', [
                'beginAirport' => $data['beginAirport']->getId(),
                'destination' => $data['destination']->getId(),
                'date' => $data['date']->format('Y-m-j'),
                'returnDate' => $returnDate,
                'trip' => $data['trip'],
            ]);
//            return $this->render('searchFlightResults.html.twig',['flights' => $flights]);
        }
        return $this->render('searchFlightForm.html.twig',[
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder) {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $encodedPassword = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encodedPassword);
            $user->setRoles(["ROLE_CUSTOMER"]);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success','Account has been made!');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('visitor/register.html.twig',[
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/search/booking", name="search_booking")
     */
    public function searchBooking(Request $request) {
        $booking = new Booking();
        $form = $this->createForm(SearchBookingFormType::class, $booking);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $r = $this->getDoctrine()->getRepository(Booking::class);
            $book = $r->findOneBy(['code' => $booking->getCode()]);

            return $this->render('visitor/searchBookingResult.html.twig',[
                'booking' => $book
            ]);
        }
        return $this->render('visitor/searchBooking.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
