<?php

namespace Tests\App\Validator;

use App\Donation\Request\DonationRequest;
use App\Repository\TransactionRepository;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\MaxFiscalYearDonationValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class MaxFiscalYearDonationValidatorTest extends ConstraintValidatorTestCase
{
    #[DataProvider('noValidateDonationProvider')]
    public function testNoValidation(?string $email, ?int $value): void
    {
        $donationRequest = $this->createDonationRequest($email);
        $donationRequest->setAmount($value);

        $this->validator->validate($donationRequest, new MaxFiscalYearDonation());

        $this->assertNoViolation();
    }

    public static function noValidateDonationProvider(): iterable
    {
        yield 'No validation if no value' => [
            'test@test.test',
            null,
        ];
        yield 'No validation if no email' => [
            null,
            50,
        ];
    }

    #[DataProvider('donationProvider')]
    public function testValidateWithNoError(
        ?int $value,
        int $maxDonation,
        int $totalCurrentAmount = 0,
    ): void {
        $donationRequest = $this->createDonationRequest();
        $donationRequest->setAmount($value);
        $this->validator = $this->createCustomValidatorSuccess($totalCurrentAmount);
        $this->validator->initialize($this->context);

        $this->validator->validate($donationRequest, new MaxFiscalYearDonation($maxDonation));

        $this->assertNoViolation();
    }

    public static function donationProvider(): iterable
    {
        yield 'No violation with no subscription 0 total donation' => [
            50,
            7500 * 100,
        ];
        yield 'No violation with no subscription max possible donation' => [
            50,
            7500 * 100,
            7450 * 100,
        ];
        yield 'No violation with subscription 0 total donation' => [
            50,
            7500 * 100,
            0,
        ];
        yield 'No violation with subscription max possible donation' => [
            50,
            7500 * 100,
            7150 * 100,
        ];
    }

    #[DataProvider('donationFailProvider')]
    public function testValidateWithError(
        array $parameters,
        ?int $value,
        int $maxDonation,
        int $totalCurrentAmount = 0,
    ): void {
        $donationRequest = $this->createDonationRequest();
        $donationRequest->setAmount($value);
        $this->validator = $this->createCustomValidatorSuccess($totalCurrentAmount);
        $this->validator->initialize($this->context);

        $this->validator->validate($donationRequest, new MaxFiscalYearDonation($maxDonation));

        $this
            ->buildViolation('donation.max_fiscal_year_donation')
            ->setParameters($parameters)
            ->assertRaised()
        ;
    }

    public static function donationFailProvider(): iterable
    {
        yield 'Violation with 0 total donation' => [
            [
                '{{ total_current_amount }}' => 0,
                '{{ max_amount_per_fiscal_year }}' => 7500,
                '{{ max_donation_remaining_possible }}' => 7500,
            ],
            8000,
            7500 * 100,
        ];
        yield 'Violation with max possible donation' => [
            [
                '{{ total_current_amount }}' => 7500,
                '{{ max_amount_per_fiscal_year }}' => 7500,
                '{{ max_donation_remaining_possible }}' => 0,
            ],
            50,
            7500 * 100,
            7500 * 100,
        ];
    }

    protected function createValidator(): MaxFiscalYearDonationValidator
    {
        return $this->createCustomValidatorFail();
    }

    protected function createCustomValidatorFail(): MaxFiscalYearDonationValidator
    {
        $transactionRepository = $this->createMock(TransactionRepository::class);

        $transactionRepository->expects($this->never())
            ->method('getTotalAmountInCentsByEmail')
        ;

        return new MaxFiscalYearDonationValidator(
            $transactionRepository
        );
    }

    protected function createCustomValidatorSuccess(int $totalCurrentAmount = 0): MaxFiscalYearDonationValidator
    {
        $transactionRepository = $this->createMock(TransactionRepository::class);

        $transactionRepository->expects($this->once())
            ->method('getTotalAmountInCentsByEmail')
            ->willReturn($totalCurrentAmount)
        ;

        return new MaxFiscalYearDonationValidator($transactionRepository);
    }

    private function createDonationRequest(?string $email = 'test@test.test'): DonationRequest
    {
        $donationRequest = new DonationRequest('123.0.0.1', 50.);
        $donationRequest->setEmailAddress($email);

        return $donationRequest;
    }
}
