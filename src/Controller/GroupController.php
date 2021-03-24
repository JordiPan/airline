<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\User;
use App\Entity\Group;
use App\Entity\GroupCustomer;
use App\Form\BookingFormType;
use App\Form\GroupCustomerFormType;
use App\Form\GroupFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    /**
     * @Route("/user/groups/", name="groups")
     */
    public function groups() {
        $em = $this->getDoctrine();
        $re = $em->getRepository(User::class);
        $rep = $em->getRepository(GroupCustomer::class);
        $r = $em->getRepository(Group::class);

        $user = $re->findOneBy(['id' => $this->getUser()->getId()]);

        $groups = $rep->findBy(['user' => $user]);
        $array = [];

        foreach ($groups as $groupC) {
            $s = $r->findOneBy(['id' => $groupC->getTheGroup()->getId()]);
            array_push($array,$s);
        }
        return $this->render('group/groups.html.twig',[
            'user' => $user,
            'groups' => $groups,
            'better' => $array
        ]);
    }
    /**
     * @Route("/user/groups/create", name="user_groups")
     */
    public function customerGroups(Request $request) {
        $group = new Group();
        $form = $this->createForm(GroupFormType::class,$group);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $group->setFounder($this->getUser());
            $em->persist($group);
            $em->flush();

            //Om de stichter in de leden te zetten
            $groupCustomer = new GroupCustomer();
            $groupCustomer->setUser($group->getFounder());
            $groupCustomer->setTheGroup($group);

            $em->persist($groupCustomer);
            $em->flush();

            return $this->redirectToRoute('groups');
        }
        return $this->render('group/groupsForm.html.twig',[
            'form' => $form->createView(),
            'action' => 'Create',
            'object' => 'group'
        ]);

    }
    /**
     * @Route("/user/groups/add/{groupId}",name="add_to_group")
     */
    public function addToGroup(Request $request, $groupId) {
        $addedCustomers = new GroupCustomer();
        $form = $this->createForm(GroupCustomerFormType::class,$addedCustomers);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $r = $this->getDoctrine()->getRepository(Group::class);
            $group = $r->findOneBy(['id' => $groupId]);

            $em = $this->getDoctrine()->getManager();

            foreach ($group->getGroupCustomers() as $customerInGroup) {
                if ($customerInGroup->getUser() === $addedCustomers->getUser()){
                    $this->addFlash('error', 'This person is already in the group!');
                    return $this->redirectToRoute('groups');
                }
            }

            $addedCustomers->setTheGroup($group);
            $em->persist($addedCustomers);
            $em->flush();

            return $this->redirectToRoute('groups');
        }

        return $this->render('group/groupsForm.html.twig',[
            'form' => $form->createView(),
            'action' => 'Add to',
            'object' => 'group'
        ]);
    }
}
