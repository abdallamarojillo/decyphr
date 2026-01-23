<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\models\Entity;
use app\models\CommunicationLink;

class EntityController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Entity::find()->orderBy(['risk_score' => SORT_DESC, 'last_seen' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $entity = Entity::findOne($id);
        if (!$entity) {
            throw new \yii\web\NotFoundHttpException('Entity not found');
        }

        // Get sent and received messages
        $sentMessages = $entity->getSentMessages()->orderBy(['intercepted_at' => SORT_DESC])->limit(10)->all();
        $receivedMessages = $entity->getReceivedMessages()->orderBy(['intercepted_at' => SORT_DESC])->limit(10)->all();

        // Get communication links
        $outgoingLinks = $entity->getOutgoingLinks()->with('targetEntity')->all();
        $incomingLinks = $entity->getIncomingLinks()->with('sourceEntity')->all();

        // Get blockchain traces
        $blockchainTraces = $entity->getBlockchainTraces()->orderBy(['timestamp' => SORT_DESC])->all();

        // Get activity timeline
        $timeline = Yii::$app->graphBuilder->buildActivityTimeline($id);

        return $this->render('view', [
            'entity' => $entity,
            'sentMessages' => $sentMessages,
            'receivedMessages' => $receivedMessages,
            'outgoingLinks' => $outgoingLinks,
            'incomingLinks' => $incomingLinks,
            'blockchainTraces' => $blockchainTraces,
            'timeline' => $timeline,
        ]);
    }

    public function actionCreate()
    {
        $entity = new Entity();

        if (Yii::$app->request->isPost) {
            $entity->load(Yii::$app->request->post());
            
            if (empty($entity->first_seen)) {
                $entity->first_seen = date('Y-m-d H:i:s');
            }
            if (empty($entity->last_seen)) {
                $entity->last_seen = date('Y-m-d H:i:s');
            }

            if ($entity->save()) {
                Yii::$app->session->setFlash('success', 'Entity created successfully');
                return $this->redirect(['view', 'id' => $entity->id]);
            }
        }

        return $this->render('create', [
            'entity' => $entity,
        ]);
    }
}
