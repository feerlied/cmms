<?php

declare(strict_types=1);

namespace Application;

enum ProcessingStatus: string {
    case SUCCESS = 'success';
    case LEXICAL_OR_SYNTACTIC_FAILURE = 'lexical_or_syntactic_failure';
    case SEMANTIC_FAILURE = 'semantic_failure';
}
