<?php
namespace rest\versions\v1\controllers;

use yii\rest\Controller;
use GuzzleHttp\Client as GuzzleClient;
use yii\web\NotFoundHttpException;

/**
 * Class UserController
 * @package rest\versions\v1\controllers
 */
class NavigationDController extends Controller
{


    protected function queryMapbox(string $path, iterable $data = null)
    {
        $guzzle = new GuzzleClient();
        $res = $guzzle->request('POST', 'https://api.mapbox.com' . $path . '?access_token=' . \Yii::$app->params['mapboxToken'] , [
            'json' => $data,
            'headers' => [
                'Content-Type'  => 'application/json',
            ],
        ]);
        return $data = json_decode($res->getBody()->__toString(), true);
    }

    public function actionOptimizeRouting($currentCoord = null) {
        $coords = '';
        $deliveryPoints = [];
        $deliveryAddress = [];
        if (null != $orders = OneCOrderProducts::myAllListByDistance(\Yii::$app->user->id, \Yii::$app->params['orderDataStart'], \Yii::$app->params['orderDataEnd'], true)) {
            foreach ($orders as $key => $order) {
                if (!in_array($order['adress_dostavki'],  $deliveryAddress)) {
                    $deliveryAddress[] = $order['adress_dostavki'];
                    $coords .= $order['long'] . ',' . $order['lat'];
                    $coords .= ';';
                    $deliveryPoints[] = [
                        'title' => $order['adress_dostavki'],
                        'coord' => [$order['long'] * 1, $order['lat'] * 1]
                    ];
                }
            }
            if (!empty($coords)) {
                $coords = substr_replace(trim($coords) ,'', -1);
            }
        }

        if ($currentCoord) {
            $coords = "{$currentCoord}{$coords}";
        }
        $params = [
            'roundtrip' => 'true',
//            'distributions' => '3,1',
            'access_token' =>  \Yii::$app->params['mapboxToken'],
            'geometries' => 'geojson',
            'language' => 'ru',
            'overview' => 'full',
            'source' => 'first',
//            'destination' => 'last',
            'steps' => 'true'
        ];
//        $coords = '13.388860,52.517037;13.397634,52.529407;13.428555,52.523219;13.418555,52.523215';
        $guzzle = new GuzzleClient();
        $res = $guzzle->request('GET', 'https://api.mapbox.com/optimized-trips/v1/mapbox/driving/' . trim($coords)  , [
            'headers' => [
                'Content-Type'  => 'application/json',
            ],
            'query' => $params
        ]);
        $data = json_decode($res->getBody()->__toString(), true);
        if (
            $data
            && !empty($data['trips'][0]['geometry']['coordinates'])

        ) {
            return [
                'route' => $data['trips'][0]['geometry']['coordinates'],
                'points' => $deliveryPoints
            ];
        } else {
            throw new NotFoundHttpException('Что-то пошло не так', 404);
        }
    }



    public function actionMatrix() {
        try {
            $coords = \Yii::$app->request->post('coords', null);
            $token = \Yii::$app->request->post('access_token', null);
            if (!$coords && !$token)
                throw new NotFoundHttpException('Неверные данные', 500);
            $data = $this->MatrixMapboxMatrix($coords, $token);
            return $data;
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage().'---'.$e->getLine().'---'.$e->getCode(), 500);
        }
    }

    protected function MatrixMapboxMatrix($coords, $token) {
        try {
            $params = [
                'access_token' => $token
            ];
            $guzzle = new GuzzleClient();
            $res = $guzzle->request('GET', 'https://api.mapbox.com/directions-matrix/v1/mapbox/driving/' . trim($coords), [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => $params
            ]);
            $data = json_decode($res->getBody()->__toString(), true);
//            return [$data];


            $arrDur = [];
            if (!empty($data['durations'])) {
                foreach ($data['durations'] as $durations) {
                    $arrDurItem = [];
                    if (is_array($durations)) {
                        foreach ($durations as $duration) {
                            if ($duration)
                                $arrDurItem[] = round($duration / 60);
                            else
                                $arrDurItem[] = $duration;
                        }
                    }
                    $arrDur[] = $arrDurItem;
                }

            }
            $data['durations'] = $arrDur;
            return $data;
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage().'---'.$e->getLine().'---'.$e->getCode(), 500);
        }
    }

    public function actionMatrixDirection() {
        try {
            $orTool = \Yii::$app->request->post('orTool', null);
            $deliveryPoints = \Yii::$app->request->post('deliveryPoints', null);
            $token = \Yii::$app->request->post('access_token', null);
            if ($orTool && !empty($orTool['result'][0])) {
                $coordsDirection = '';
                foreach ($orTool['result'][0] as $orToolItem) {

                    if (array_key_exists($orToolItem['index'], $deliveryPoints)){
                        $coordsDirection .= $deliveryPoints[$orToolItem['index']]['coord'];
                        $addressQueue[] = $deliveryPoints[$orToolItem['index']];
                    }

                }
                if (!empty($coordsDirection)) {
                    $coordsDirection = substr_replace(trim($coordsDirection) ,'', -1);
                }
                $params = [
                    'access_token' =>  $token,
                    'geometries' => 'geojson',
                    'language' => 'ru',
                    'overview' => 'full',
                    'steps' => 'true'
                ];
                $guzzle = new GuzzleClient();
                $res = $guzzle->request('GET', 'https://api.mapbox.com/directions/v5/mapbox/driving/' . trim($coordsDirection)  , [
                    'headers' => [
                        'Content-Type'  => 'application/json',
                    ],
                    'query' => $params
                ]);
                $dataRes = json_decode($res->getBody()->__toString(), true);

                return compact('dataRes', 'addressQueue');
            }
            throw new NotFoundHttpException('orTool is empty', 500);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage().'---'.$e->getLine().'---'.$e->getCode(), 500);
        }
    }

}
