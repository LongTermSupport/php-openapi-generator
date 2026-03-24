<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\NormalizerGenerator as BaseNormalizerGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Normalizer\DenormalizerGenerator as DenormalizerGeneratorTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Normalizer\NormalizerGenerator as NormalizerGeneratorTrait;

class NormalizerGenerator extends BaseNormalizerGenerator
{
    use DenormalizerGeneratorTrait;
    use NormalizerGeneratorTrait;
}
