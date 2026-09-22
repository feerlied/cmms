<?php

declare(strict_types=1);

namespace Diagnostic;

enum DiagnosticSeverity: string {
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFORMATION = 'information';
    case HINT = 'hint';
}
