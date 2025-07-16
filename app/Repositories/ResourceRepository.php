<?php

/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2024. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Repositories;

use App\Factory\ResourceFactory;
use App\Models\Resource;

/**
 * ResourceRepository.
 */
class ResourceRepository extends BaseRepository
{
    /**
     * Saves the resource and its data.
     *
     * @param      array                     $data     The data
     * @param      \App\Models\Resource      $resource The resource
     *
     * @return     \App\Models\Resource
     */
    public function save(array $data, Resource $resource): Resource
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $resource->fill($data);

        $resource->saveQuietly();

        return $resource;
    }

    /**
     * Store resources in bulk.
     *
     * @param array $resource
     *
     * @return \App\Models\Resource|null
     */
    public function create($resource): ?Resource
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return $this->save(
            $resource,
            ResourceFactory::create($user->company()->id, $user->id)
        );
    }

    public function delete($resource): Resource
    {
        parent::delete($resource);

        return $resource;
    }
} 