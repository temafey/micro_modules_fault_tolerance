<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue;

use Exception;

/**
 * DataProvider for class {testClassName}.
 */
class QueueFaultTolerantProducerDataProvider
{
    /**
     * Return test data for AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer.
     *
     * @return mixed[]
     */
    public function getDataForSendEventMethod(): array
    {
        return [
              0 => [
                0 => [
                  'ProducerInterface' => [
                    'sendEvent' => null,
                    'sendCommand' => null,
                  ],
                  'CircuitBreakerInterface' => [
                    'isAvailable' => true,
                    'isBlocked' => false,
                    'reportFailure' => null,
                    'reportSuccess' => null,
                    'getMaxFailures' => 8,
                    'getCriticalFailures' => 8,
                    'getRetryTimeout' => '00:59:35',
                    'getFailures' => 8,
                    'getLastTest' => 8,
                  ],
                  'DeepCopy' => [
                    'copy' => 'aliquid',
                    'addFilter' => 'aliquid',
                    'prependFilter' => 'aliquid',
                    'addTypeFilter' => 'aliquid',
                  ],
                  'retryTimeout' => '11:18:14',
                  'topic' => 'consequatur',
                  'message' => 'ullam',
                ],
                1 => [
                  'ProducerInterface' => [
                    'times' => 0,
                    'sendEvent' => 1,
                  ],
                  'CircuitBreakerInterface' => [
                    'times' => 0,
                    'isAvailable' => 1,
                    'isBlocked' => 0,
                    'reportFailure' => 0,
                    'reportSuccess' => 0,
                    'getMaxFailures' => 0,
                    'getCriticalFailures' => 0,
                    'getRetryTimeout' => 0,
                    'getFailures' => 0,
                    'getLastTest' => 0,
                  ],
                  'DeepCopy' => [
                    'times' => 0,
                    'copy' => 1,
                    'addFilter' => 0,
                    'prependFilter' => 0,
                    'addTypeFilter' => 0,
                  ],
                    'LoggerInterface' => [
                        'warning' => 0,
                    ],
                ],
              ],
            ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer.
     *
     * @return mixed[]
     */
    public function getDataForSendEventIsAvailableFalseMethod(): array
    {
        return [
            0 => [
                0 => [
                    'ProducerInterface' => [
                        'sendEvent' => null,
                        'sendCommand' => null,
                    ],
                    'CircuitBreakerInterface' => [
                        'isAvailable' => [false, false, true],
                        'isBlocked' => [false, false],
                        'reportFailure' => null,
                        'reportSuccess' => null,
                        'getMaxFailures' => 8,
                        'getCriticalFailures' => 8,
                        'getRetryTimeout' => '00:59:35',
                        'getFailures' => 8,
                        'getLastTest' => 8,
                    ],
                    'DeepCopy' => [
                        'copy' => 'aliquid',
                        'addFilter' => 'aliquid',
                        'prependFilter' => 'aliquid',
                        'addTypeFilter' => 'aliquid',
                    ],
                    'retryTimeout' => '11:18:14',
                    'topic' => 'consequatur',
                    'message' => 'ullam',
                ],
                1 => [
                    'ProducerInterface' => [
                        'times' => 0,
                        'sendEvent' => 1,
                    ],
                    'CircuitBreakerInterface' => [
                        'times' => 0,
                        'isAvailable' => 3,
                        'isBlocked' => 2,
                        'reportFailure' => 0,
                        'reportSuccess' => 0,
                        'getMaxFailures' => 0,
                        'getCriticalFailures' => 0,
                        'getRetryTimeout' => 0,
                        'getFailures' => 0,
                        'getLastTest' => 0,
                    ],
                    'DeepCopy' => [
                        'times' => 0,
                        'copy' => 1,
                        'addFilter' => 0,
                        'prependFilter' => 0,
                        'addTypeFilter' => 0,
                    ],
                    'LoggerInterface' => [
                        'warning' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer.
     *
     * @return mixed[]
     */
    public function getDataForSendEventIsAvailableFalseIsBlockedTrueMethod(): array
    {
        return [
            0 => [
                0 => [
                    'ProducerInterface' => [
                        'sendEvent' => null,
                        'sendCommand' => null,
                    ],
                    'CircuitBreakerInterface' => [
                        'isAvailable' => [false, false, false],
                        'isBlocked' => [false, false, true],
                        'reportFailure' => null,
                        'reportSuccess' => null,
                        'getMaxFailures' => 8,
                        'getCriticalFailures' => 8,
                        'getRetryTimeout' => '00:59:35',
                        'getFailures' => 8,
                        'getLastTest' => 8,
                    ],
                    'DeepCopy' => [
                        'copy' => 'aliquid',
                        'addFilter' => 'aliquid',
                        'prependFilter' => 'aliquid',
                        'addTypeFilter' => 'aliquid',
                    ],
                    'retryTimeout' => '11:18:14',
                    'topic' => 'consequatur',
                    'message' => 'ullam',
                ],
                1 => [
                    'ProducerInterface' => [
                        'times' => 0,
                        'sendEvent' => 0,
                    ],
                    'CircuitBreakerInterface' => [
                        'times' => 0,
                        'isAvailable' => 3,
                        'isBlocked' => 3,
                        'reportFailure' => 0,
                        'reportSuccess' => 0,
                        'getMaxFailures' => 0,
                        'getCriticalFailures' => 0,
                        'getRetryTimeout' => 0,
                        'getFailures' => 0,
                        'getLastTest' => 0,
                    ],
                    'DeepCopy' => [
                        'times' => 0,
                        'copy' => 1,
                        'addFilter' => 0,
                        'prependFilter' => 0,
                        'addTypeFilter' => 0,
                    ],
                    'LoggerInterface' => [
                        'warning' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer.
     *
     * @return mixed[]
     */
    public function getDataForSendEventIsAvailableFalseIsBlockedTrueWithExceptionMethod(): array
    {
        return [
            0 => [
                0 => [
                    'ProducerInterface' => [
                        'sendEvent' => ['exception' => Exception::class],
                        'sendCommand' => null,
                    ],
                    'CircuitBreakerInterface' => [
                        'isAvailable' => [true, true, false],
                        'isBlocked' => [false, true],
                        'reportFailure' => null,
                        'reportSuccess' => null,
                        'getMaxFailures' => 8,
                        'getCriticalFailures' => 8,
                        'getRetryTimeout' => '00:59:35',
                        'getFailures' => 8,
                        'getLastTest' => 8,
                    ],
                    'DeepCopy' => [
                        'copy' => 'aliquid',
                        'addFilter' => 'aliquid',
                        'prependFilter' => 'aliquid',
                        'addTypeFilter' => 'aliquid',
                    ],
                    'retryTimeout' => '11:18:14',
                    'topic' => 'consequatur',
                    'message' => 'ullam',
                ],
                1 => [
                    'ProducerInterface' => [
                        'times' => 0,
                        'sendEvent' => 2,
                    ],
                    'CircuitBreakerInterface' => [
                        'times' => 0,
                        'isAvailable' => 4,
                        'isBlocked' => 2,
                        'reportFailure' => 2,
                        'reportSuccess' => 0,
                        'getMaxFailures' => 0,
                        'getCriticalFailures' => 0,
                        'getRetryTimeout' => 0,
                        'getFailures' => 0,
                        'getLastTest' => 0,
                    ],
                    'DeepCopy' => [
                        'times' => 0,
                        'copy' => 2,
                        'addFilter' => 0,
                        'prependFilter' => 0,
                        'addTypeFilter' => 0,
                    ],
                    'LoggerInterface' => [
                        'warning' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer.
     *
     * @return mixed[]
     */
    public function getDataForSendCommandMethod(): array
    {
        return [
              0 => [
                0 => [
                  'ProducerInterface' => [
                    'sendEvent' => null,
                    'sendCommand' => [
                      'receive' => [
                        'getBody' => 'consectetur',
                        'setBody' => null,
                        'setProperties' => null,
                        'getProperties' => 'minus',
                        'setProperty' => null,
                        'getProperty' => 'minus',
                        'setHeaders' => null,
                        'getHeaders' => 'minus',
                        'setHeader' => null,
                        'getHeader' => 'minus',
                        'setRedelivered' => null,
                        'isRedelivered' => false,
                        'setCorrelationId' => null,
                        'getCorrelationId' => 'consectetur',
                        'setMessageId' => null,
                        'getMessageId' => 'consectetur',
                        'getTimestamp' => '19:48:30',
                        'setTimestamp' => '11:41:20',
                        'setReplyTo' => null,
                        'getReplyTo' => 'consectetur',
                      ],
                      'receiveNoWait' => [
                        'getBody' => 'pariatur',
                        'setBody' => null,
                        'setProperties' => null,
                        'getProperties' => 'libero',
                        'setProperty' => null,
                        'getProperty' => 'libero',
                        'setHeaders' => null,
                        'getHeaders' => 'libero',
                        'setHeader' => null,
                        'getHeader' => 'libero',
                        'setRedelivered' => null,
                        'isRedelivered' => true,
                        'setCorrelationId' => null,
                        'getCorrelationId' => 'pariatur',
                        'setMessageId' => null,
                        'getMessageId' => 'pariatur',
                        'getTimestamp' => '12:51:52',
                        'setTimestamp' => '03:11:07',
                        'setReplyTo' => null,
                        'getReplyTo' => 'pariatur',
                      ],
                      'setDeleteReplyQueue' => 'qui',
                      'isDeleteReplyQueue' => true,
                    ],
                  ],
                  'CircuitBreakerInterface' => [
                    'isAvailable' => true,
                    'isBlocked' => false,
                    'reportFailure' => null,
                    'reportSuccess' => null,
                    'getMaxFailures' => 8,
                    'getCriticalFailures' => 8,
                    'getRetryTimeout' => '00:59:35',
                    'getFailures' => 8,
                    'getLastTest' => 8,
                  ],
                  'DeepCopy' => [
                    'copy' => 'aliquid',
                    'addFilter' => 'aliquid',
                    'prependFilter' => 'aliquid',
                    'addTypeFilter' => 'aliquid',
                  ],
                  'retryTimeout' => '11:18:14',
                  'sendCommand' => null,
                  'command' => 'quae',
                  'message' => null,
                  'needReply' => false,
                ],
                1 => [
                  'ProducerInterface' => [
                    'times' => 0,
                    'sendEvent' => 0,
                    'sendCommand' => [
                      'times' => 1,
                      'receive' => [
                        'times' => 0,
                        'getBody' => 0,
                        'setBody' => 0,
                        'setProperties' => 0,
                        'getProperties' => 0,
                        'setProperty' => 0,
                        'getProperty' => 0,
                        'setHeaders' => 0,
                        'getHeaders' => 0,
                        'setHeader' => 0,
                        'getHeader' => 0,
                        'setRedelivered' => 0,
                        'isRedelivered' => 0,
                        'setCorrelationId' => 0,
                        'getCorrelationId' => 0,
                        'setMessageId' => 0,
                        'getMessageId' => 0,
                        'getTimestamp' => 0,
                        'setTimestamp' => 0,
                        'setReplyTo' => 0,
                        'getReplyTo' => 0,
                      ],
                      'receiveNoWait' => [
                        'times' => 0,
                        'getBody' => 0,
                        'setBody' => 0,
                        'setProperties' => 0,
                        'getProperties' => 0,
                        'setProperty' => 0,
                        'getProperty' => 0,
                        'setHeaders' => 0,
                        'getHeaders' => 0,
                        'setHeader' => 0,
                        'getHeader' => 0,
                        'setRedelivered' => 0,
                        'isRedelivered' => 0,
                        'setCorrelationId' => 0,
                        'getCorrelationId' => 0,
                        'setMessageId' => 0,
                        'getMessageId' => 0,
                        'getTimestamp' => 0,
                        'setTimestamp' => 0,
                        'setReplyTo' => 0,
                        'getReplyTo' => 0,
                      ],
                      'setDeleteReplyQueue' => 0,
                      'isDeleteReplyQueue' => 0,
                    ],
                  ],
                  'CircuitBreakerInterface' => [
                    'times' => 0,
                    'isAvailable' => 1,
                    'isBlocked' => 0,
                    'reportFailure' => 0,
                    'reportSuccess' => 0,
                    'getMaxFailures' => 0,
                    'getCriticalFailures' => 0,
                    'getRetryTimeout' => 0,
                    'getFailures' => 0,
                    'getLastTest' => 0,
                  ],
                  'DeepCopy' => [
                    'times' => 0,
                    'copy' => 1,
                    'addFilter' => 0,
                    'prependFilter' => 0,
                    'addTypeFilter' => 0,
                  ],
                  'LoggerInterface' => [
                      'warning' => 0,
                  ],
                  'sendCommand' => 0,
                ],
              ],
            ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer.
     *
     * @return mixed[]
     */
    public function getDataForSendCommandIsAvailableFalseIsBlockedFalseMethod(): array
    {
        return [
            0 => [
                0 => [
                    'ProducerInterface' => [
                        'sendEvent' => null,
                        'sendCommand' => [
                            'receive' => [
                                'getBody' => 'consectetur',
                                'setBody' => null,
                                'setProperties' => null,
                                'getProperties' => 'minus',
                                'setProperty' => null,
                                'getProperty' => 'minus',
                                'setHeaders' => null,
                                'getHeaders' => 'minus',
                                'setHeader' => null,
                                'getHeader' => 'minus',
                                'setRedelivered' => null,
                                'isRedelivered' => false,
                                'setCorrelationId' => null,
                                'getCorrelationId' => 'consectetur',
                                'setMessageId' => null,
                                'getMessageId' => 'consectetur',
                                'getTimestamp' => '19:48:30',
                                'setTimestamp' => '11:41:20',
                                'setReplyTo' => null,
                                'getReplyTo' => 'consectetur',
                            ],
                            'receiveNoWait' => [
                                'getBody' => 'pariatur',
                                'setBody' => null,
                                'setProperties' => null,
                                'getProperties' => 'libero',
                                'setProperty' => null,
                                'getProperty' => 'libero',
                                'setHeaders' => null,
                                'getHeaders' => 'libero',
                                'setHeader' => null,
                                'getHeader' => 'libero',
                                'setRedelivered' => null,
                                'isRedelivered' => true,
                                'setCorrelationId' => null,
                                'getCorrelationId' => 'pariatur',
                                'setMessageId' => null,
                                'getMessageId' => 'pariatur',
                                'getTimestamp' => '12:51:52',
                                'setTimestamp' => '03:11:07',
                                'setReplyTo' => null,
                                'getReplyTo' => 'pariatur',
                            ],
                            'setDeleteReplyQueue' => 'qui',
                            'isDeleteReplyQueue' => true,
                        ],
                    ],
                    'CircuitBreakerInterface' => [
                        'isAvailable' => [false, false, true],
                        'isBlocked' => [false, false],
                        'reportFailure' => null,
                        'reportSuccess' => null,
                        'getMaxFailures' => 8,
                        'getCriticalFailures' => 8,
                        'getRetryTimeout' => '00:59:35',
                        'getFailures' => 8,
                        'getLastTest' => 8,
                    ],
                    'DeepCopy' => [
                        'copy' => 'aliquid',
                        'addFilter' => 'aliquid',
                        'prependFilter' => 'aliquid',
                        'addTypeFilter' => 'aliquid',
                    ],
                    'retryTimeout' => '11:18:14',
                    'command' => 'consequatur',
                    'message' => 'ullam',
                ],
                1 => [
                    'ProducerInterface' => [
                        'times' => 0,
                        'sendCommand' => [
                            'times' => 1,
                            'receive' => [
                                'times' => 0,
                                'getBody' => 0,
                                'setBody' => 0,
                                'setProperties' => 0,
                                'getProperties' => 0,
                                'setProperty' => 0,
                                'getProperty' => 0,
                                'setHeaders' => 0,
                                'getHeaders' => 0,
                                'setHeader' => 0,
                                'getHeader' => 0,
                                'setRedelivered' => 0,
                                'isRedelivered' => 0,
                                'setCorrelationId' => 0,
                                'getCorrelationId' => 0,
                                'setMessageId' => 0,
                                'getMessageId' => 0,
                                'getTimestamp' => 0,
                                'setTimestamp' => 0,
                                'setReplyTo' => 0,
                                'getReplyTo' => 0,
                            ],
                            'receiveNoWait' => [
                                'times' => 0,
                                'getBody' => 0,
                                'setBody' => 0,
                                'setProperties' => 0,
                                'getProperties' => 0,
                                'setProperty' => 0,
                                'getProperty' => 0,
                                'setHeaders' => 0,
                                'getHeaders' => 0,
                                'setHeader' => 0,
                                'getHeader' => 0,
                                'setRedelivered' => 0,
                                'isRedelivered' => 0,
                                'setCorrelationId' => 0,
                                'getCorrelationId' => 0,
                                'setMessageId' => 0,
                                'getMessageId' => 0,
                                'getTimestamp' => 0,
                                'setTimestamp' => 0,
                                'setReplyTo' => 0,
                                'getReplyTo' => 0,
                            ],
                            'setDeleteReplyQueue' => 0,
                            'isDeleteReplyQueue' => 0,
                        ],
                    ],
                    'CircuitBreakerInterface' => [
                        'times' => 0,
                        'isAvailable' => 3,
                        'isBlocked' => 2,
                        'reportFailure' => 0,
                        'reportSuccess' => 0,
                        'getMaxFailures' => 0,
                        'getCriticalFailures' => 0,
                        'getRetryTimeout' => 0,
                        'getFailures' => 0,
                        'getLastTest' => 0,
                    ],
                    'DeepCopy' => [
                        'times' => 0,
                        'copy' => 1,
                        'addFilter' => 0,
                        'prependFilter' => 0,
                        'addTypeFilter' => 0,
                    ],
                    'LoggerInterface' => [
                        'warning' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer.
     *
     * @return mixed[]
     */
    public function getDataForSendCommandIsAvailableFalseIsBlockedTrueMethod(): array
    {
        return [
            0 => [
                0 => [
                    'ProducerInterface' => [
                        'sendEvent' => null,
                        'sendCommand' => [
                            'receive' => [
                                'getBody' => 'consectetur',
                                'setBody' => null,
                                'setProperties' => null,
                                'getProperties' => 'minus',
                                'setProperty' => null,
                                'getProperty' => 'minus',
                                'setHeaders' => null,
                                'getHeaders' => 'minus',
                                'setHeader' => null,
                                'getHeader' => 'minus',
                                'setRedelivered' => null,
                                'isRedelivered' => false,
                                'setCorrelationId' => null,
                                'getCorrelationId' => 'consectetur',
                                'setMessageId' => null,
                                'getMessageId' => 'consectetur',
                                'getTimestamp' => '19:48:30',
                                'setTimestamp' => '11:41:20',
                                'setReplyTo' => null,
                                'getReplyTo' => 'consectetur',
                            ],
                            'receiveNoWait' => [
                                'getBody' => 'pariatur',
                                'setBody' => null,
                                'setProperties' => null,
                                'getProperties' => 'libero',
                                'setProperty' => null,
                                'getProperty' => 'libero',
                                'setHeaders' => null,
                                'getHeaders' => 'libero',
                                'setHeader' => null,
                                'getHeader' => 'libero',
                                'setRedelivered' => null,
                                'isRedelivered' => true,
                                'setCorrelationId' => null,
                                'getCorrelationId' => 'pariatur',
                                'setMessageId' => null,
                                'getMessageId' => 'pariatur',
                                'getTimestamp' => '12:51:52',
                                'setTimestamp' => '03:11:07',
                                'setReplyTo' => null,
                                'getReplyTo' => 'pariatur',
                            ],
                            'setDeleteReplyQueue' => 'qui',
                            'isDeleteReplyQueue' => true,
                        ],
                    ],
                    'CircuitBreakerInterface' => [
                        'isAvailable' => [false, false, false],
                        'isBlocked' => [false, false, true],
                        'reportFailure' => null,
                        'reportSuccess' => null,
                        'getMaxFailures' => 8,
                        'getCriticalFailures' => 8,
                        'getRetryTimeout' => '00:59:35',
                        'getFailures' => 8,
                        'getLastTest' => 8,
                    ],
                    'DeepCopy' => [
                        'copy' => 'aliquid',
                        'addFilter' => 'aliquid',
                        'prependFilter' => 'aliquid',
                        'addTypeFilter' => 'aliquid',
                    ],
                    'retryTimeout' => '11:18:14',
                    'command' => 'consequatur',
                    'message' => 'ullam',
                ],
                1 => [
                    'ProducerInterface' => [
                        'times' => 0,
                        'sendCommand' => [
                            'times' => 0,
                            'receive' => [
                                'times' => 0,
                                'getBody' => 0,
                                'setBody' => 0,
                                'setProperties' => 0,
                                'getProperties' => 0,
                                'setProperty' => 0,
                                'getProperty' => 0,
                                'setHeaders' => 0,
                                'getHeaders' => 0,
                                'setHeader' => 0,
                                'getHeader' => 0,
                                'setRedelivered' => 0,
                                'isRedelivered' => 0,
                                'setCorrelationId' => 0,
                                'getCorrelationId' => 0,
                                'setMessageId' => 0,
                                'getMessageId' => 0,
                                'getTimestamp' => 0,
                                'setTimestamp' => 0,
                                'setReplyTo' => 0,
                                'getReplyTo' => 0,
                            ],
                            'receiveNoWait' => [
                                'times' => 0,
                                'getBody' => 0,
                                'setBody' => 0,
                                'setProperties' => 0,
                                'getProperties' => 0,
                                'setProperty' => 0,
                                'getProperty' => 0,
                                'setHeaders' => 0,
                                'getHeaders' => 0,
                                'setHeader' => 0,
                                'getHeader' => 0,
                                'setRedelivered' => 0,
                                'isRedelivered' => 0,
                                'setCorrelationId' => 0,
                                'getCorrelationId' => 0,
                                'setMessageId' => 0,
                                'getMessageId' => 0,
                                'getTimestamp' => 0,
                                'setTimestamp' => 0,
                                'setReplyTo' => 0,
                                'getReplyTo' => 0,
                            ],
                            'setDeleteReplyQueue' => 0,
                            'isDeleteReplyQueue' => 0,
                        ],
                    ],
                    'CircuitBreakerInterface' => [
                        'times' => 0,
                        'isAvailable' => 3,
                        'isBlocked' => 3,
                        'reportFailure' => 0,
                        'reportSuccess' => 0,
                        'getMaxFailures' => 0,
                        'getCriticalFailures' => 0,
                        'getRetryTimeout' => 0,
                        'getFailures' => 0,
                        'getLastTest' => 0,
                    ],
                    'DeepCopy' => [
                        'times' => 0,
                        'copy' => 1,
                        'addFilter' => 0,
                        'prependFilter' => 0,
                        'addTypeFilter' => 0,
                    ],
                    'LoggerInterface' => [
                        'warning' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer.
     *
     * @return mixed[]
     */
    public function getDataForSendCommandIsAvailableFalseIsBlockedTrueWithExceptionMethod(): array
    {
        return [
            0 => [
                0 => [
                    'ProducerInterface' => [
                        'sendEvent' => null,
                        'sendCommand' => [
                            'exception' => Exception::class,
                            'receive' => [
                                'getBody' => 'consectetur',
                                'setBody' => null,
                                'setProperties' => null,
                                'getProperties' => 'minus',
                                'setProperty' => null,
                                'getProperty' => 'minus',
                                'setHeaders' => null,
                                'getHeaders' => 'minus',
                                'setHeader' => null,
                                'getHeader' => 'minus',
                                'setRedelivered' => null,
                                'isRedelivered' => false,
                                'setCorrelationId' => null,
                                'getCorrelationId' => 'consectetur',
                                'setMessageId' => null,
                                'getMessageId' => 'consectetur',
                                'getTimestamp' => '19:48:30',
                                'setTimestamp' => '11:41:20',
                                'setReplyTo' => null,
                                'getReplyTo' => 'consectetur',
                            ],
                            'receiveNoWait' => [
                                'getBody' => 'pariatur',
                                'setBody' => null,
                                'setProperties' => null,
                                'getProperties' => 'libero',
                                'setProperty' => null,
                                'getProperty' => 'libero',
                                'setHeaders' => null,
                                'getHeaders' => 'libero',
                                'setHeader' => null,
                                'getHeader' => 'libero',
                                'setRedelivered' => null,
                                'isRedelivered' => true,
                                'setCorrelationId' => null,
                                'getCorrelationId' => 'pariatur',
                                'setMessageId' => null,
                                'getMessageId' => 'pariatur',
                                'getTimestamp' => '12:51:52',
                                'setTimestamp' => '03:11:07',
                                'setReplyTo' => null,
                                'getReplyTo' => 'pariatur',
                            ],
                            'setDeleteReplyQueue' => 'qui',
                            'isDeleteReplyQueue' => true,
                        ],
                    ],
                    'CircuitBreakerInterface' => [
                        'isAvailable' => [true, true, false],
                        'isBlocked' => [false, true],
                        'reportFailure' => null,
                        'reportSuccess' => null,
                        'getMaxFailures' => 8,
                        'getCriticalFailures' => 8,
                        'getRetryTimeout' => '00:59:35',
                        'getFailures' => 8,
                        'getLastTest' => 8,
                    ],
                    'DeepCopy' => [
                        'copy' => 'aliquid',
                        'addFilter' => 'aliquid',
                        'prependFilter' => 'aliquid',
                        'addTypeFilter' => 'aliquid',
                    ],
                    'retryTimeout' => '11:18:14',
                    'command' => 'consequatur',
                    'message' => 'ullam',
                ],
                1 => [
                    'ProducerInterface' => [
                        'times' => 0,
                        'sendCommand' => [
                            'times' => 2,
                            'receive' => [
                                'times' => 0,
                                'getBody' => 0,
                                'setBody' => 0,
                                'setProperties' => 0,
                                'getProperties' => 0,
                                'setProperty' => 0,
                                'getProperty' => 0,
                                'setHeaders' => 0,
                                'getHeaders' => 0,
                                'setHeader' => 0,
                                'getHeader' => 0,
                                'setRedelivered' => 0,
                                'isRedelivered' => 0,
                                'setCorrelationId' => 0,
                                'getCorrelationId' => 0,
                                'setMessageId' => 0,
                                'getMessageId' => 0,
                                'getTimestamp' => 0,
                                'setTimestamp' => 0,
                                'setReplyTo' => 0,
                                'getReplyTo' => 0,
                            ],
                            'receiveNoWait' => [
                                'times' => 0,
                                'getBody' => 0,
                                'setBody' => 0,
                                'setProperties' => 0,
                                'getProperties' => 0,
                                'setProperty' => 0,
                                'getProperty' => 0,
                                'setHeaders' => 0,
                                'getHeaders' => 0,
                                'setHeader' => 0,
                                'getHeader' => 0,
                                'setRedelivered' => 0,
                                'isRedelivered' => 0,
                                'setCorrelationId' => 0,
                                'getCorrelationId' => 0,
                                'setMessageId' => 0,
                                'getMessageId' => 0,
                                'getTimestamp' => 0,
                                'setTimestamp' => 0,
                                'setReplyTo' => 0,
                                'getReplyTo' => 0,
                            ],
                            'setDeleteReplyQueue' => 0,
                            'isDeleteReplyQueue' => 0,
                        ],
                    ],
                    'CircuitBreakerInterface' => [
                        'times' => 0,
                        'isAvailable' => 4,
                        'isBlocked' => 2,
                        'reportFailure' => 2,
                        'reportSuccess' => 0,
                        'getMaxFailures' => 0,
                        'getCriticalFailures' => 0,
                        'getRetryTimeout' => 0,
                        'getFailures' => 0,
                        'getLastTest' => 0,
                    ],
                    'DeepCopy' => [
                        'times' => 0,
                        'copy' => 2,
                        'addFilter' => 0,
                        'prependFilter' => 0,
                        'addTypeFilter' => 0,
                    ],
                    'LoggerInterface' => [
                        'warning' => 1,
                    ],
                ],
            ],
        ];
    }
}
