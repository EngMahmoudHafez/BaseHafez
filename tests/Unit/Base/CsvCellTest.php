<?php

use App\Modules\Base\Support\CsvCell;

it('escapes spreadsheet formula prefixes without changing safe values', function () {
    expect(CsvCell::escape('=2+2'))->toBe("'=2+2");
    expect(CsvCell::escape('+SUM(A1:A2)'))->toBe("'+SUM(A1:A2)");
    expect(CsvCell::escape('@command'))->toBe("'@command");
    expect(CsvCell::escape('Safe value'))->toBe('Safe value');
    expect(CsvCell::escape(42))->toBe(42);
});
