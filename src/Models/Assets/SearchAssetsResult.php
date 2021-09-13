<?php

namespace Rootsoft\Algorand\Models\Assets;

/**
 * Class SearchAssetsResult.
 */
class SearchAssetsResult
{
    /**
     * Round at which the results were computed.
     * @var int
     * @required
     */
    public int $currentRound = 0;

    /**
     * Used for pagination, when making another request provide this token with the next parameter.
     * @var string|null
     */
    public ?string $nextToken;

    /**
     * A list of assets.
     * @var Asset[]
     * @required
     */
    public array $assets;
}
