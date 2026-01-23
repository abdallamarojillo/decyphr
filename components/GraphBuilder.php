<?php

namespace app\components;

use yii\base\Component;
use app\models\Entity;
use app\models\CommunicationLink;
use app\models\Message;

class GraphBuilder extends Component
{
    /**
     * Build communication network graph data
     */
    public function buildCommunicationGraph($entityIds = null, $options = [])
    {
        $nodes = [];
        $edges = [];

        // Get entities
        $entityQuery = Entity::find();
        if ($entityIds) {
            $entityQuery->where(['id' => $entityIds]);
        }
        $entities = $entityQuery->all();

        // Build nodes
        foreach ($entities as $entity) {
            $nodes[] = [
                'id' => $entity->id,
                'label' => $entity->name ?: $entity->entity_code,
                'title' => $this->buildNodeTooltip($entity),
                'group' => $entity->entity_type,
                'value' => $entity->risk_score,
                'color' => $this->getNodeColor($entity->risk_score),
                'shape' => $this->getNodeShape($entity->entity_type),
            ];
        }

        // Get communication links
        $linkQuery = CommunicationLink::find();
        if ($entityIds) {
            $linkQuery->where(['source_entity_id' => $entityIds])
                     ->orWhere(['target_entity_id' => $entityIds]);
        }
        $links = $linkQuery->all();

        // Build edges
        foreach ($links as $link) {
            $edges[] = [
                'from' => $link->source_entity_id,
                'to' => $link->target_entity_id,
                'value' => $link->message_count,
                'title' => $this->buildEdgeTooltip($link),
                'color' => $this->getEdgeColor($link->link_strength),
                'width' => max(1, $link->link_strength / 10),
            ];
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'options' => $this->getGraphOptions($options)
        ];
    }

    /**
     * Build timeline data for entity activity
     */
    public function buildActivityTimeline($entityId)
    {
        $entity = Entity::findOne($entityId);
        if (!$entity) {
            return [];
        }

        $timeline = [];

        // Get sent messages
        $sentMessages = Message::find()
            ->where(['source_id' => $entityId])
            ->orderBy(['intercepted_at' => SORT_ASC])
            ->all();

        foreach ($sentMessages as $msg) {
            $timeline[] = [
                'date' => $msg->intercepted_at,
                'type' => 'sent',
                'content' => substr($msg->encrypted_content, 0, 50) . '...',
                'status' => $msg->status,
                'destination' => $msg->destination ? $msg->destination->name : 'Unknown'
            ];
        }

        // Get received messages
        $receivedMessages = Message::find()
            ->where(['destination_id' => $entityId])
            ->orderBy(['intercepted_at' => SORT_ASC])
            ->all();

        foreach ($receivedMessages as $msg) {
            $timeline[] = [
                'date' => $msg->intercepted_at,
                'type' => 'received',
                'content' => substr($msg->encrypted_content, 0, 50) . '...',
                'status' => $msg->status,
                'source' => $msg->source ? $msg->source->name : 'Unknown'
            ];
        }

        // Sort by date
        usort($timeline, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $timeline;
    }

    /**
     * Calculate network statistics
     */
    public function calculateNetworkStats($entityIds = null)
    {
        $query = Entity::find();
        if ($entityIds) {
            $query->where(['id' => $entityIds]);
        }

        $totalEntities = $query->count();
        $highRiskEntities = $query->andWhere(['>=', 'risk_score', 75])->count();

        $linkQuery = CommunicationLink::find();
        if ($entityIds) {
            $linkQuery->where(['source_entity_id' => $entityIds])
                     ->orWhere(['target_entity_id' => $entityIds]);
        }

        $totalLinks = $linkQuery->count();
        $totalMessages = $linkQuery->sum('message_count') ?: 0;

        return [
            'total_entities' => $totalEntities,
            'high_risk_entities' => $highRiskEntities,
            'total_links' => $totalLinks,
            'total_messages' => $totalMessages,
            'average_risk_score' => round(Entity::find()->average('risk_score'), 2),
            'network_density' => $this->calculateNetworkDensity($totalEntities, $totalLinks)
        ];
    }

    /**
     * Find central entities (most connected)
     */
    public function findCentralEntities($limit = 10)
    {
        $entities = Entity::find()
            ->select(['entities.*', 'COUNT(communication_links.id) as connection_count'])
            ->leftJoin('communication_links', 
                'entities.id = communication_links.source_entity_id OR entities.id = communication_links.target_entity_id')
            ->groupBy('entities.id')
            ->orderBy(['connection_count' => SORT_DESC])
            ->limit($limit)
            ->all();

        return $entities;
    }

    /**
     * Build node tooltip
     */
    private function buildNodeTooltip($entity)
    {
        $html = "<strong>{$entity->name}</strong><br>";
        $html .= "Code: {$entity->entity_code}<br>";
        $html .= "Type: {$entity->entity_type}<br>";
        $html .= "Risk Score: {$entity->risk_score}<br>";
        $html .= "Last Seen: {$entity->last_seen}";
        return $html;
    }

    /**
     * Build edge tooltip
     */
    private function buildEdgeTooltip($link)
    {
        $html = "Messages: {$link->message_count}<br>";
        $html .= "Link Strength: {$link->link_strength}<br>";
        $html .= "First Contact: {$link->first_contact}<br>";
        $html .= "Last Contact: {$link->last_contact}";
        return $html;
    }

    /**
     * Get node color based on risk score
     */
    private function getNodeColor($riskScore)
    {
        if ($riskScore >= 75) {
            return '#dc3545'; // Red
        } elseif ($riskScore >= 50) {
            return '#ffc107'; // Yellow
        } elseif ($riskScore >= 25) {
            return '#17a2b8'; // Blue
        } else {
            return '#28a745'; // Green
        }
    }

    /**
     * Get node shape based on entity type
     */
    private function getNodeShape($entityType)
    {
        switch ($entityType) {
            case 'person':
                return 'dot';
            case 'group':
                return 'diamond';
            case 'device':
                return 'box';
            default:
                return 'dot';
        }
    }

    /**
     * Get edge color based on link strength
     */
    private function getEdgeColor($linkStrength)
    {
        if ($linkStrength >= 75) {
            return ['color' => '#dc3545', 'opacity' => 1];
        } elseif ($linkStrength >= 50) {
            return ['color' => '#ffc107', 'opacity' => 0.8];
        } else {
            return ['color' => '#6c757d', 'opacity' => 0.5];
        }
    }

    /**
     * Get graph visualization options
     */
    private function getGraphOptions($customOptions = [])
    {
        $defaultOptions = [
            'nodes' => [
                'borderWidth' => 2,
                'borderWidthSelected' => 4,
                'font' => [
                    'size' => 14,
                    'color' => '#000000'
                ]
            ],
            'edges' => [
                'arrows' => [
                    'to' => [
                        'enabled' => true,
                        'scaleFactor' => 0.5
                    ]
                ],
                'smooth' => [
                    'type' => 'continuous'
                ]
            ],
            'physics' => [
                'enabled' => true,
                'barnesHut' => [
                    'gravitationalConstant' => -8000,
                    'centralGravity' => 0.3,
                    'springLength' => 200,
                    'springConstant' => 0.04
                ]
            ],
            'interaction' => [
                'hover' => true,
                'tooltipDelay' => 100,
                'navigationButtons' => true,
                'keyboard' => true
            ]
        ];

        return array_merge($defaultOptions, $customOptions);
    }

    /**
     * Calculate network density
     */
    private function calculateNetworkDensity($nodes, $edges)
    {
        if ($nodes <= 1) {
            return 0;
        }
        $maxEdges = $nodes * ($nodes - 1);
        return round(($edges / $maxEdges) * 100, 2);
    }
}
