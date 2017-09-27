<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Person;
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
     * @Rest\Get(path = "/people", name = "api_GET_all")
     * @Rest\View()
     */
    public function indexAction()
    {
        return $this->getDoctrine()->getRepository('AppBundle:Person')->findAll();
    }

    /**
     * @Rest\Get(
     *     path="/people/{id}",
     *     name="api_GET",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View
     */
    public function personAction(Person $person)
    {
        return $person;
    }

    /**
     * @Rest\Post(path = "/people", name = "api_POST")
     * @Rest\View(StatusCode = Response::HTTP_CREATED)
     * @ParamConverter("person", converter="fos_rest.request_body")
     */
    public function postPersonAction(Person $person, ConstraintViolationList $validation)
    {
        if($validation->count()) {
            return $this->view($validation, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($person);
        $em->flush();

        return $this->view($person, Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('api_GET', ['id' => $person->getId()])
            ]
        );
    }

    /**
     * @Rest\Delete(
     *     path="/people/{id}",
     *     name="api_DELETE",
     *     requirements={"id"="\d+"}
     * )
     *
     * @Rest\View(StatusCode = Response::HTTP_NO_CONTENT)
     */
    public function deletePersonAction(Person $person)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($person);
        $em->flush();
    }

    /**
     * @Rest\Put(path = "/people/{id}", name = "api_put")
     * @ParamConverter("person", class="AppBundle\Entity\Person")
     * @ParamConverter("personUpdate", converter="fos_rest.request_body")
     *
     * @Rest\View(StatusCode = Response::HTTP_NO_CONTENT)
     */
    public function putPersonAction(Person $person, Person $personUpdate, ConstraintViolationList $validation)
    {
        if($validation->count()) {
            return $this->view($validation, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $em->merge($this->updatePerson($person, $personUpdate));
        $em->flush();

        return $this->view($person, Response::HTTP_NO_CONTENT,
            [
                'Location' => $this->generateUrl('api_GET', ['id' => $person->getId()])
            ]
        );
    }

    private function updatePerson(Person $person, Person $personNew)
    {
        $person->setName($personNew->getName());
        $person->setMail($personNew->getMail());

        return $person;
    }

}
