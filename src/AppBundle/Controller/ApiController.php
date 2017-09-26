<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiController
 * @package AppBundle\Controller
 *
 * @Route("/api")
 */
class ApiController extends FOSRestController
{

    /**
     * @Rest\Get(path = "/people", name = "api_people")
     * @Rest\View()
     */
    public function indexAction()
    {
        return $this->getDoctrine()->getRepository('AppBundle:Person')->findAll();
    }

    /**
     * @Rest\Get(
     *     path="/people/{id}",
     *     name="api_people_id",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View
     */
    public function druidAction(Person $person)
    {
        return $person;
    }

    /**
     * @Rest\Post(path = "/people", name = "api_people_add")
     * @Rest\View(StatusCode = Response::HTTP_CREATED)
     * @ParamConverter("person", converter="fos_rest.request_body")
     */
    public function postDruidAction(Person $person, ConstraintViolationList $validation)
    {
        if($validation->count()) {
            return $this->view($validation, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($person);
        $em->flush();

        return $this->view($person, Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('api_people_id', ['id' => $person->getId()])
            ]
        );
    }

    /**
     * @Rest\Delete(
     *     path="/people/{id}",
     *     name="api_delete_person",
     *     requirements={"id"="\d+"}
     * )
     *
     * @Rest\View(StatusCode = Response::HTTP_NO_CONTENT)
     */
    public function deleteDruidAction(Person $person)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($person);
        $em->flush();
    }
}
