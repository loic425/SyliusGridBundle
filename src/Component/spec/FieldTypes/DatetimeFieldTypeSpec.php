<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Component\Grid\Tests\Unit\FieldTypes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Grid\DataExtractor\DataExtractorInterface;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\DatetimeFieldType;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DatetimeFieldTypeTest extends TestCase
{
    private DataExtractorInterface|MockObject $dataExtractorMock;

    private DatetimeFieldType $datetimeFieldType;

    protected function setUp(): void
    {
        $this->dataExtractorMock = $this->createMock(DataExtractorInterface::class);
        $this->datetimeFieldType = new DatetimeFieldType($this->dataExtractorMock);
    }

    public function testAGridFieldType(): void
    {
        $this->assertInstanceOf(FieldTypeInterface::class, $this->datetimeFieldType);
    }

    public function testUsesDataExtractorToObtainDataParseItWithGivenConfigurationAndRendersIt(): void
    {
        /** @var \DateTime|MockObject $dateTimeMock */
        $dateTimeMock = $this->createMock(\DateTime::class);

        /** @var Field|MockObject $fieldMock */
        $fieldMock = $this->createMock(Field::class);

        $this->dataExtractorMock->expects($this->once())->method('get')->with($fieldMock, ['foo' => 'bar'])->willReturn($dateTimeMock);
        $dateTimeMock->expects($this->never())->method('setTimezone');
        $dateTimeMock->expects($this->once())->method('format')->with('Y-m-d')->willReturn('2001-10-10');

        $this->assertSame('2001-10-10', $this->datetimeFieldType->render($fieldMock, ['foo' => 'bar'], [
            'format' => 'Y-m-d',
            'timezone' => null,
        ]));
    }

    public function testSetsTimezoneIfSpecified(): void
    {
        /** @var \DateTime|MockObject $dateTimeMock */
        $dateTimeMock = $this->createMock(\DateTime::class);

        /** @var Field|MockObject $fieldMock */
        $fieldMock = $this->createMock(Field::class);

        $this->dataExtractorMock->expects($this->once())->method('get')->with($fieldMock, ['foo' => 'bar'])->willReturn($dateTimeMock);
        $dateTimeMock->expects($this->once())->method('setTimezone')->with(new \DateTimeZone('Europe/Warsaw'))->willReturn($dateTimeMock);
        $dateTimeMock->expects($this->once())->method('format')->with('Y-m-d H:i:s')->willReturn('2021-10-10 00:00:00');

        $this->assertSame('2021-10-10 00:00:00', $this->datetimeFieldType->render($fieldMock, ['foo' => 'bar'], [
            'format' => 'Y-m-d H:i:s',
            'timezone' => 'Europe/Warsaw',
        ]));
    }

    public function testReturnsNullIfPropertyAccessorReturnsNull(): void
    {
        /** @var Field|MockObject $fieldMock */
        $fieldMock = $this->createMock(Field::class);

        $this->dataExtractorMock->expects($this->once())->method('get')->with($fieldMock, ['foo' => 'bar'])->willReturn(null);

        $this->assertSame('', $this->datetimeFieldType->render($fieldMock, ['foo' => 'bar'], [
            'format' => '',
            'timezone' => null,
        ]));
    }

    public function testUsesTimezoneParameterAsDefaultTimezoneOption(): void
    {
        /** @var OptionsResolver|MockObject $resolverMock */
        $resolverMock = $this->createMock(OptionsResolver::class);

        $this->datetimeFieldType = new DatetimeFieldType($this->dataExtractorMock, 'Europe/Warsaw');

        $resolverMock->expects($this->exactly(2))
            ->method('setDefault')
            ->willReturnMap([
                ['format', 'Y-m-d H:i:s', $resolverMock],
                ['timezone', 'Europe/Warsaw', $resolverMock],
            ])
        ;
        $resolverMock->expects($this->exactly(3))
            ->method('setAllowedTypes')
            ->willReturnMap([
                ['format', 'string', $resolverMock],
                ['timezone', ['null', 'string'], $resolverMock],
                ['vars', 'array', $resolverMock],
            ])
        ;

        $this->datetimeFieldType->configureOptions($resolverMock);
    }

    public function testThrowsExceptionIfReturnedValueIsNotDatetime(): void
    {
        /** @var Field|MockObject $fieldMock */
        $fieldMock = $this->createMock(Field::class);

        $this->dataExtractorMock->expects($this->once())
            ->method('get')
            ->with($fieldMock, ['foo' => 'bar'])
            ->willReturn('badObject')
        ;
        $this->expectException(\InvalidArgumentException::class);

        $this->datetimeFieldType->render($fieldMock, ['foo' => 'bar'], [
            'format' => '',
            'timezone' => null,
        ]);
    }
}
