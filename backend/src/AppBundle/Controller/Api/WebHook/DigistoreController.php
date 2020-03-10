<?php


namespace AppBundle\Controller\Api\WebHook;


use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;


/**
 * Class DigistoreController
 * @package AppBundle\Controller\Api\WebHook
 *
 * @Rest\Route("/digistore")
 */
class DigistoreController extends FOSRestController
{

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/webhook/digistore/",
     *   tags={"WEBHOOK DIGISTORE"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     * @Rest\Post("/")
     * @SWG\Post(path="/v2/webhook/digistore/",
     *   tags={"WEBHOOK DIGISTORE"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function ipnAction(Request $request){
        $fs = new Filesystem();
        $ipn_data = $request->request->all();
        $fs->appendToFile('digistore_request.txt', "DATA:"."\n".json_encode($ipn_data)."\n\n");

        $received_signature = $request->request->get('sha_sign');
        $expected_signature = $this->digistore_signature($ipn_data);

        $sha_sign_valid = $received_signature == $expected_signature;
        if (!$sha_sign_valid)
        {
            return $this->handleView($this->view('ERROR: invalid sha signature', Response::HTTP_BAD_REQUEST));
        }

        $em = $this->getDoctrine()->getManager();

        $event = $request->request->get('event');
        switch ($event) {
            case 'connection_test': {
                return $this->handleView($this->view(null, Response::HTTP_OK));
            }

            case 'on_payment': {
                $order_id = $request->request->get('order_id');
                $product_id = $request->request->get('product_id');
                $email = $request->request->get('email');

                if(!empty($order_id) && !empty($product_id) && !empty($email)){
                    $user = $em->getRepository("AppBundle:User")->findOneBy(['email'=>$email]);
                    if($user instanceof User){
                        if($product_id == '277520'){
                            if($user->getProduct() instanceof DigistoreProduct){
                                $quantity = 1;
                                if($request->request->has('quantity') && $request->request->get('quantity') > 0){
                                    $quantity = $request->request->get('quantity');
                                }
                                $user->setLimitSubscribers($user->getProduct()->getLimitSubscribers() + (5000*$quantity));
                                $em->persist($user);
                                $em->flush();

                                return $this->handleView($this->view(null, Response::HTTP_OK));
                            }
                            else{
                                return $this->handleView($this->view('User have not basic product', Response::HTTP_BAD_REQUEST));
                            }
                        }
                        else{
                            $digistoreProduct = $em->getRepository("AppBundle:DigistoreProduct")->findOneBy(['productId'=>$product_id]);
                            if($digistoreProduct instanceof DigistoreProduct){
                                $user->setOrderId($order_id);
                                $user->setProduct($digistoreProduct);
                                $user->setLimitSubscribers($digistoreProduct->getLimitSubscribers());
                                $user->setTrialEnd(null);

                                $em->persist($user);
                                $em->flush();

                                return $this->handleView($this->view(null, Response::HTTP_OK));
                            }
                            else{
                                return $this->handleView($this->view('Digistore Product not Found', Response::HTTP_BAD_REQUEST));
                            }
                        }
                    }
                    else{
                        return $this->handleView($this->view('User not Found', Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    $message = '';
                    if(!empty($order_id)){
                        $message .= 'order_id is empty! ';
                    }
                    if(!empty($product_id)){
                        $message .= 'product_id is empty! ';
                    }
                    if(!empty($email)){
                        $message .= 'email is empty!';
                    }

                    return $this->handleView($this->view($message, Response::HTTP_BAD_REQUEST));
                }
                break;
            }

            case 'on_payment_missed':
            case 'on_refund':
            case 'on_chargeback': {
                $order_id = $request->request->get('order_id');
                $product_id = $request->request->get('product_id');
                $email = $request->request->get('email');

                if($product_id == '277520'){
                    if(!empty($email)){
                        $user = $em->getRepository("AppBundle:User")->findOneBy(['email'=>$email]);
                        if($user instanceof User){
                            if($user->getProduct() instanceof DigistoreProduct){
                                $user->setLimitSubscribers($user->getProduct()->getLimitSubscribers());
                                $em->persist($user);
                                $em->flush();
                            }

                            return $this->handleView($this->view(null, Response::HTTP_OK));
                        }
                    }

                    return $this->handleView($this->view('User not Found', Response::HTTP_BAD_REQUEST));
                }
                else{
                    $user = $em->getRepository("AppBundle:User")->findOneBy(['orderId'=>$order_id]);
                    if($user instanceof User){
                        $user->setOrderId(null);
                        $user->setProduct(null);
                        $user->setLimitSubscribers(0);

                        $em->persist($user);
                        $em->flush();

                        return $this->handleView($this->view(null, Response::HTTP_OK));
                    }

                    return $this->handleView($this->view('User not Found', Response::HTTP_BAD_REQUEST));
                }

                break;
            }
            default: {
                return $this->handleView($this->view(null, Response::HTTP_OK));
            }
        }
    }

    /**
     * @param $array
     * @return string
     */
    private function digistore_signature($array){
        $ipn_passphrase = $this->container->getParameter('digistore_ipn_passphrase');
        unset($array[ 'sha_sign' ]);
        $keys = array_keys($array);
        sort($keys);
        $sha_string = "";
        foreach ($keys as $key)
        {
            $value = html_entity_decode( $array[ $key ] );
            $is_empty = !isset($value) || $value === "" || $value === false;

            if ($is_empty)
            {
                continue;
            }

            $sha_string .= "$key=$value$ipn_passphrase";
        }
        $sha_sign = strtoupper(hash("sha512", $sha_string));

        return $sha_sign;
    }
}
