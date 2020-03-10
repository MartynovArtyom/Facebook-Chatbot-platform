<?php


namespace AppBundle\Controller\Api\Site;


use AppBundle\Entity\Contract;
use AppBundle\Helper\Digistore\DigistoreApi;
use AppBundle\Helper\Digistore\DigistoreApiException;
use Dompdf\Dompdf;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class ContractController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/contract")
 */
class ContractController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     * @throws
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/contract/",
     *   tags={"Contract"},
     *   security=false,
     *   summary="GET contract",
     *   description="The method for getting contract",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="contracts",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="title",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="url",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="created",
     *                      type="datetime",
     *                  ),
     *              )
     *          ),
     *          @SWG\Property(
     *              property="customer",
     *              type="object",
     *              @SWG\Property(
     *                  property="company",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="street",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="zipcode",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="city",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="country",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="orderID",
     *                  type="string",
     *              ),
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   )
     * )
     */
    public function listAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $resultContracts = $em->getRepository('AppBundle:Contract')->findBy(['user'=>$this->getUser()], ['created'=>'DESC']);
        $contracts = [];
        if(!empty($resultContracts)){
            foreach ($resultContracts as $contract){
                if($contract instanceof Contract){
                    $contracts[] = [
                        'id' => $contract->getId(),
                        'title' => $contract->getTitle(),
                        'url' => $contract->getUrl(),
                        'created' => $contract->getCreated()
                    ];
                }
            }
        }
        $customer = $this->getCustomerData();

        $view = $this->view([
            'contracts' => $contracts,
            'customer' => $customer
        ], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/")
     * @SWG\Post(path="/v2/contract/",
     *   tags={"Contract"},
     *   security=false,
     *   summary="CREATE contract",
     *   description="The method for create contract",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="title",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="url",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="created",
     *              type="datetime",
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=500,
     *      description="INTERNAL ERROR",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     * )
     */
    public function createAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $customer = $this->getCustomerData();
        if(!empty($customer)){
            $now = new \DateTime();
            $dompdf = new Dompdf(array('enable_remote' => true));
            $dompdf->loadHtml($this->renderView('contract/contract.html.twig',[
                'customer' => $customer,
                'now' => $now
            ]));
            $dompdf->render();

            $fileSystem = new Filesystem();
            if(!$fileSystem->exists("uploads/user/".$this->getUser()->getId())){
                $fileSystem->mkdir("uploads/user/".$this->getUser()->getId());
            }
            $fileName = "ChatBo-Vertrag_".$now->format('Y-m-d_H_i');
            $link = "uploads/user/".$this->getUser()->getId()."/".$fileName.".pdf";
            if(file_put_contents($link, $dompdf->output())){
                $contract = new Contract($this->getUser(), $fileName, $request->getSchemeAndHttpHost()."/".$link, $now);
                $em->persist($contract);
                $em->flush();

                $view = $this->view([
                    'id' => $contract->getId(),
                    'title' => $contract->getTitle(),
                    'url' => $contract->getUrl(),
                    'created' => $contract->getCreated()
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error' => [
                        'message' => 'Ups! Irgendwas lief schief. Bitte versuche es erneut'
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error' => [
                    'message' => 'Digistore details not found'
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Delete("/{id}", requirements={"id"="\d+"})
     * @SWG\Delete(path="/v2/contract/{id}",
     *   tags={"Contract"},
     *   security=false,
     *   summary="DELETE contract",
     *   description="The method for delete contract",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default=0,
     *      description="id"
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success.",
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="Not found",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=500,
     *      description="INTERNAL ERROR",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     * )
     */
    public function removeByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $contract = $em->getRepository("AppBundle:Contract")->findOneBy(['user'=>$this->getUser(), 'id'=>$id]);
        if($contract instanceof Contract){
            try{
                $link = parse_url($contract->getUrl());
                if(isset($link['path'])){
                    $fileSystem = new Filesystem();
                    if($fileSystem->exists(ltrim($link['path'], '/'))){
                        $fileSystem->remove(ltrim($link['path'], '/'));
                    }
                }
            }
            catch (\Exception $e){}
            $em->remove($contract);
            $em->flush();

            $view = $this->view([], Response::HTTP_NO_CONTENT);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error' => [
                    'message' => 'Contract Not Found'
                ]
            ], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/show")
     * @SWG\Get(path="/v2/contract/show",
     *   tags={"Contract"},
     *   security=false,
     *   summary="show contract",
     *   description="The method for show contract",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success."
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=500,
     *      description="INTERNAL ERROR",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     * )
     */
    public function showAction(Request $request){
        $customer = $this->getCustomerData();
        if(!empty($customer)){
            $now = new \DateTime();
            $dompdf = new Dompdf(array('enable_remote' => true));
            $dompdf->loadHtml($this->renderView('contract/contract.html.twig',[
                'customer' => $customer,
                'now' => $now
            ]));
            $dompdf->render();

            return new Response($dompdf->output(),200,[
                'Content-type' => 'application/pdf'
            ]);
        }
        else{
            $view = $this->view([
                'error' => [
                    'message' => 'Digistore details not found'
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
    }

    /**
     * @return array
     */
    private function getCustomerData(){
        if(!empty($this->getUser()->getOrderId())){
            try {
                $api = DigistoreApi::connect($this->container->getParameter('digistore_api_key'));
                $customerData = $api->getPurchase($this->getUser()->getOrderId());
                $api->disconnect();
                if(isset($customerData->buyer) && !empty($customerData->buyer)){
                    $buyer = $customerData->buyer;
                    return [
                        'company' => isset($buyer->company) ? $buyer->company : '',
                        'firstName' => isset($buyer->first_name) ? $buyer->first_name : '',
                        'lastName' => isset($buyer->last_name) ? $buyer->last_name : '',
                        'street' => isset($buyer->street) ? $buyer->street : '',
                        'zipcode' => isset($buyer->zipcode) ? $buyer->zipcode : '',
                        'city' => isset($buyer->city) ? $buyer->city : '',
                        'country' => isset($buyer->country) ? $buyer->country : '',
                        'orderID' => isset($customerData->id) ? $customerData->id : '',
                    ];
                }
            }
            catch (DigistoreApiException $e) {}
        }

        return [];
    }
}
