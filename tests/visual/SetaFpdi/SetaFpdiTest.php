<?php

namespace setasign\tests\visual\SetaFpdi;


use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PageBoundaries;
use setasign\SetaFpdf\SetaFpdi;
use setasign\tests\TestProxy;
use setasign\tests\VisualTestCase;

class SetaFpdiTest extends VisualTestCase
{
    const PDF_FOLDER = __DIR__ . '/../../files/pdfs/';

    /**
     * @return TestProxy
     * @throws \InvalidArgumentException
     */
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new TestProxy([
            new Fpdi($orientation, $unit, $size),
            new SetaFpdi($orientation, $unit, $size),
        ]);
    }

    public function testDataProvider()
    {
        return [
            [
                [
                    'Boombastic-Box.pdf' =>  __DIR__ . '/../../../assets/pdfs/Boombastic-Box.pdf'
                ],
                [
                    [
                        'src' => [
                            'file' => 'Boombastic-Box.pdf',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => false,
                            'pageBreak' => false
                        ]
                    ]
                ]
            ],
            [
                [
                    '1000.pdf' => __DIR__ . '/../../../assets/pdfs/1000.pdf'
                ],
                [
                    [
                        'src' => [
                            'file' => '1000.pdf',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => false,
                            'pageBreak' => false
                        ]
                    ]
                ]
            ],
            [
                [
                    '-90' => __DIR__ . '/../../../assets/pdfs/rotated/-90.pdf',
                    '-180' => __DIR__ . '/../../../assets/pdfs/rotated/-180.pdf',
                    '-270' => __DIR__ . '/../../../assets/pdfs/rotated/-270.pdf',
                    '-360' => __DIR__ . '/../../../assets/pdfs/rotated/-360.pdf',
                    '-450' => __DIR__ . '/../../../assets/pdfs/rotated/-450.pdf',
                    '90' => __DIR__ . '/../../../assets/pdfs/rotated/90.pdf',
                    '180' => __DIR__ . '/../../../assets/pdfs/rotated/180.pdf',
                    '270' => __DIR__ . '/../../../assets/pdfs/rotated/270.pdf',
                    '360' => __DIR__ . '/../../../assets/pdfs/rotated/360.pdf',
                    '450' => __DIR__ . '/../../../assets/pdfs/rotated/450.pdf',
                    'all' => __DIR__ . '/../../../assets/pdfs/rotated/all.pdf ',
                ],
                [
                    [
                        'src' => [
                            'file' => '-90',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true
                        ]
                    ],
                    [
                        'src' => [
                            'file' => '-180',
                            'pageNo' => 1,
                            'box' => PageBoundaries::ART_BOX, // fallback?
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true,
                        ]
                    ],
                    [
                        'src' => [
                            'file' => '-270',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true,
                        ]
                    ],
                    [
                        'src' => [
                            'file' => '-360',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true,
                        ]
                    ],
                    [
                        'src' => [
                            'file' => '-450',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true,
                        ]
                    ],
                    [
                        'src' => [
                            'file' => '90',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true,
                        ]
                    ],
                    [
                        'src' => [
                            'file' => '180',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true,
                        ]
                    ],
                    [
                        'src' => [
                            'file' => '360',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true,
                        ]
                    ],
                    [
                        'src' => [
                            'file' => '450',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => false,
                        ]
                    ]

                ]
            ],
            [
                [
                    'All' => __DIR__ . '/../../../assets/pdfs/boxes/all.pdf',
                ],
                [
                    [
                        'src' => [
                            'file' => 'All',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true
                        ]
                    ],
                    [
                        'src' => [
                            'file' => 'All',
                            'pageNo' => 1,
                            'box' => PageBoundaries::ART_BOX,
                            'group' => true
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true
                        ]
                    ],
                    [
                        'src' => [
                            'file' => 'All',
                            'pageNo' => 1,
                            'box' => PageBoundaries::BLEED_BOX,
                            'group' => true
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true
                        ]
                    ],
                    [
                        'src' => [
                            'file' => 'All',
                            'pageNo' => 1,
                            'box' => PageBoundaries::MEDIA_BOX,
                            'group' => true
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => true
                        ]
                    ],
                    [
                        'src' => [
                            'file' => 'All',
                            'pageNo' => 1,
                            'box' => PageBoundaries::TRIM_BOX,
                            'group' => true
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => true,
                            'pageBreak' => false
                        ]
                    ]
                ]
            ],
            [
                [
                    '1' => __DIR__ . '/../../../assets/pdfs/boxes/[-100 -100 1000 1000].pdf'
                ],
                [
                    [
                        'src' => [
                            'file' => '1',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => false,
                            'pageBreak' => true
                        ]
                    ]
                ]
            ],
            [
                [
                    'transparent' => __DIR__ . '/../../../assets/pdfs/transparency/ex74.pdf'
                ],
                [
                    [
                        'src' => [
                            'file' => 'transparent',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => true,
                        ],
                        'target' => [
                            'position' => [0, 0],
                            'size' => [null, null],
                            'adjustPageSize' => false,
                            'pageBreak' => false
                        ]
                    ],
                    [
                        'src' => [
                            'file' => 'transparent',
                            'pageNo' => 1,
                            'box' => PageBoundaries::CROP_BOX,
                            'group' => false,
                        ],
                        'target' => [
                            'position' => [70, 0],
                            'size' => [null, null],
                            'adjustPageSize' => false,
                            'pageBreak' => false
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider testDataProvider
     */
    public function testImport($files, $pages)
    {
        $proxy = $this->getProxy();
        $proxy->AddPage();

        $pageIds = [];

        foreach ($pages as $page) {
            $src = $page['src'];
            $index = $src['file'] . '|' . $src['pageNo'] . '|' . $src['box'] . '|' . ($src['group'] ? 1 : 0);
            if (!isset($pageIds[$index])) {
                $proxy->setSourceFile($files[$src['file']]);
                $pageIds[$index] = $proxy->importPage(
                    $src['pageNo'],
                    $src['box'],
                    $src['group']
                );
            }

            $target = $page['target'];
            $proxy->useImportedPage(
                $pageIds[$index],
                $target['position'][0],
                $target['position'][1],
                $target['size'][0],
                $target['size'][1],
                $target['adjustPageSize']
            );

            if ($target['pageBreak']) {
                $proxy->AddPage();
            }
        }

        $this->assertProxySame($proxy, VisualTestCase::TOLERANCE, 60);
    }
}