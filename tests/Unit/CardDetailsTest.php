<?php

use DK\MerchantSuite\Data\BankAccount;
use DK\MerchantSuite\Data\CardDetails;
use DK\MerchantSuite\Data\Expiry;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

it('parses expiry formats', function (string $in, string $month, string $year) {
    $expiry = Expiry::fromString($in);

    expect($expiry->month)->toBe($month)->and($expiry->year)->toBe($year);
})->with([
    ['0529', '05', '29'],
    ['05/29', '05', '29'],
    ['5/29', '05', '29'],
    ['05-2029', '05', '29'],
    ['9945', '99', '45'], // test-mode bank response simulation
]);

it('rejects invalid expiries', function (string $in) {
    Expiry::fromString($in);
})->with(['1329', '0029', '05/2', 'May 29'])->throws(InvalidArgumentException::class);

it('strips spaces and dashes from card numbers', function () {
    $card = new CardDetails('5123 4567-8901 2346', '05/29', '123');

    expect($card->toArray())->toBe([
        'number' => '5123456789012346',
        'expiry' => ['month' => '05', 'year' => '29'],
        'cvn' => '123',
    ]);
});

it('rejects malformed card data', function () {
    expect(fn () => new CardDetails('1234', '05/29'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new CardDetails('5123456789012346', '05/29', '12'))->toThrow(InvalidArgumentException::class);
});

it('keeps the card number and cvn out of every kind of dump', function (Closure $dump) {
    $output = $dump(new CardDetails('5123456789012346', '05/29', '987', 'Jane Citizen'));

    expect($output)->not->toContain('5123456789012346')
        ->not->toContain('987');
})->with([
    'dd() / dump() / error pages' => fn ($card) => (new CliDumper)->dump((new VarCloner)->cloneVar($card), true),
    'var_dump' => function ($card) {
        ob_start();
        var_dump($card);

        return (string) ob_get_clean();
    },
    'var_export' => fn ($card) => var_export($card, true),
    'print_r' => fn ($card) => print_r($card, true),
    'json_encode' => fn ($card) => (string) json_encode($card),
    '(array) cast' => fn ($card) => (string) json_encode((array) $card),
]);

it('shows only the masked number', function () {
    $card = new CardDetails('5123456789012346', '05/29', '987');

    expect($card->masked())->toBe('512345...346')
        ->and((new CliDumper)->dump((new VarCloner)->cloneVar($card), true))->toContain('512345...346');
});

it('still sends the real values to the gateway', function () {
    expect((new CardDetails('5123456789012346', '05/29', '987'))->toArray())
        ->toMatchArray(['number' => '5123456789012346', 'cvn' => '987']);
});

it('refuses to be cloned', function () {
    clone new CardDetails('5123456789012346', '05/29');
})->throws(LogicException::class);

it('refuses to be serialised', function () {
    serialize(new CardDetails('5123456789012346', '05/29'));
})->throws(LogicException::class);

it('hides the card number from stack traces', function () {
    ini_set('zend.exception_ignore_args', '0');

    try {
        new CardDetails('5123456789012346', '05/29', 'bad'); // invalid CVN throws inside the constructor
    } catch (InvalidArgumentException $e) {
        $frame = collect($e->getTrace())->firstWhere('function', '__construct');

        expect($frame)->not->toBeNull()
            ->and($frame['args'][0])->toBeInstanceOf(SensitiveParameterValue::class)
            ->and($frame['args'][2])->toBeInstanceOf(SensitiveParameterValue::class)
            ->and(collect($e->getTrace())->pluck('args')->flatten(1)->filter(fn ($a) => is_string($a))->contains('5123456789012346'))->toBeFalse();

        return;
    }

    $this->fail('Expected an exception.');
});

it('masks bank accounts in dumps', function () {
    $account = new BankAccount('062-000', '12345678', 'J Citizen');

    expect((new CliDumper)->dump((new VarCloner)->cloneVar($account), true))
        ->not->toContain('12345678')
        ->toContain('...678')
        ->and($account->toArray())->toBe(['bsb' => '062000', 'account' => '12345678', 'name' => 'J Citizen']);
});

it('validates bank details', function () {
    expect(fn () => new BankAccount('06200', '123'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new BankAccount('062000', '12-34'))->toThrow(InvalidArgumentException::class);
});
