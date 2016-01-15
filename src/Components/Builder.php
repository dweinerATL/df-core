<?php

namespace DreamFactory\Core\Components;

use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Core\Models\BaseModel;
use DreamFactory\Core\Database\RelationSchema;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

class Builder extends EloquentBuilder
{
    /**
     * Get the relation instance for the given relation name.
     *
     * @param  string $relation
     *
     * @return Relation
     * @throws BadRequestException
     */
    public function getRelation($relation)
    {
        logger('In Componets/Builder/getRelation');
        logger('Passed in relation: ' . print_r($relation, true));
        $query = Relation::noConstraints(
            function () use ($relation){
                /** @var BaseModel $model */
                $model = $this->getModel();
                $relationType = $model->getReferencingType($relation);
                logger('Model: ' . print_r($model, true));
                logger('Relation Type: ' . print_r($relationType, true));
                if (RelationSchema::HAS_MANY === $relationType) {
                    $tmp = $model->getHasManyByRelationName($relation);
                    logger('Relation: ' . print_r($tmp, true));
                    return $tmp;
                } elseif (RelationSchema::MANY_MANY === $relationType) {
                    return $model->getBelongsToManyByRelationName($relation);
                } elseif (RelationSchema::BELONGS_TO === $relationType) {
                    return $model->getBelongsToByRelationName($relation);
                }

                return null;
            }
        );

        if (!empty($query)) {
            return $query;
        }

        if (!method_exists($this->getModel(), $relation)) {
            throw new BadRequestException('Unknown relationship: ' . $relation);
        }

        return parent::getRelation($relation);
    }
}