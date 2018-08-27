<?php
namespace CannaPlan\Helpers;


class PlanData
{
    public static function get_json_data()
    {
        $json=array();
        $json["chapter"]=array(
            0=>[
                "name"=>"Executive Summary",
                "order"=>"1",
                "section"=>array(
                    0=>[
                        "name"=>"Opportunity",
                        "order"=>"1",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Problem",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ],
                            1=>[
                                "name"=>"Solution",
                                "order"=>"2",
                                "content_type"=>"topic"
                            ],
                            2=>[
                                "name"=>"Market",
                                "order"=>"3",
                                "content_type"=>"topic"
                            ],
                            3=>[
                                "name"=>"Competition",
                                "order"=>"4",
                                "content_type"=>"topic"
                            ],
                            4=>[
                                "name"=>"Why Us?",
                                "order"=>"5",
                                "content_type"=>"topic"
                            ]
                        )

                    ],
                    1=>[
                        "name"=>"Expectations",
                        "order"=>"2",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Forecast",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ],
                            1=>[
                                "name"=>"Financial Highlights by Year",
                                "order"=>"2",
                                "content_type"=>"chart",
                                "content_id"=>"3"
                            ],
                            2=>[
                                "name"=>"Financing Needed",
                                "order"=>"3",
                                "content_type"=>"topic"
                            ]
                        )
                    ]
                    )
                ],
            1=>[
                "name"=>"Opportunity",
                "order"=>"2",
                "section"=>array(
                    0=>[
                        "name"=>"Problem & Solution",
                        "order"=>"1",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Problem Worth Solving",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ],
                            1=>[
                                "name"=>"Our solution",
                                "order"=>"2",
                                "content_type"=>"topic"
                            ]

                        )
                    ],
                    1=>[
                        "name"=>"Target Market",
                        "order"=>"2",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Market Size & Segments",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ]
                        )
                    ],
                    2=>[
                        "name"=>"Competition",
                        "order"=>"3",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Current alternatives",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ],
                            1=>[
                                "name"=>"Our advantages",
                                "order"=>"2 ",
                                "content_type"=>"topic"
                            ]
                        )
                    ]

                )
            ],
            2=>[
                "name"=>"Execution",
                "order"=>"3",
                "section"=>array(
                    0=>[
                        "name"=>"Marketing & Sales",
                        "order"=>"1",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Marketing Plan",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ],
                            1=>[
                                "name"=>"Sales Plan",
                                "order"=>"2",
                                "content_type"=>"topic"
                            ]

                        )
                    ],
                    1=>[
                        "name"=>"Operations",
                        "order"=>"2",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Locations & Facilities",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ],
                            1=>[
                                "name"=>"Technology",
                                "order"=>"2",
                                "content_type"=>"topic"
                            ],
                            2=>[
                                "name"=>"Equipment & Tools",
                                "order"=>"3",
                                "content_type"=>"topic"
                            ]
                        )
                    ],
                    2=>[
                        "name"=>"Milestones & Metrics",
                        "order"=>"3",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Milestones Table",
                                "order"=>"1",
                                "content_type"=>"table",
                                "content_id"=>"1"
                            ],
                            1=>[
                                "name"=>"Key metrics",
                                "order"=>"2 ",
                                "content_type"=>"topic"
                            ]
                        )
                    ]

                )
            ],
            3=>[
                "name"=>"Company",
                "order"=>"4",
                "section"=>array(
                    0=>[
                        "name"=>"Overview",
                        "order"=>"1",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Ownership & Structure",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ]

                        )
                    ],
                    1=>[
                        "name"=>"Team",
                        "order"=>"2",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Management team",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ],
                            1=>[
                                "name"=>"Advisors",
                                "order"=>"2",
                                "content_type"=>"topic"
                            ]
                        )
                    ]

                )
            ],
            4=>[
                "name"=>"Financial Plan",
                "order"=>"5",
                "section"=>array(
                    0=>[
                        "name"=>"Forecast",
                        "order"=>"1",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Key assumptions",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ],
                            1=>[
                                "name"=>"Revenue",
                                "order"=>"2",
                                "content_type"=>"chart",
                                "content_id"=>"6"
                            ],
                            2=>[
                                "name"=>"Expenses",
                                "order"=>"3",
                                "content_type"=>"chart",
                                "content_id"=>"2"
                            ],
                            3=>[
                                "name"=>"Net Profit (or Loss)",
                                "order"=>"4",
                                "content_type"=>"chart",
                                "content_id"=>"5"
                            ]


                        )
                    ],
                    1=>[
                        "name"=>"Financing",
                        "order"=>"2",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Use of funds",
                                "order"=>"1",
                                "content_type"=>"topic"
                            ],
                            1=>[
                                "name"=>"Sources of Funds",
                                "order"=>"2",
                                "content_type"=>"topic"
                            ]
                        )
                    ],
                    2=>[
                        "name"=>"Statements",
                        "order"=>"3",
                        "section_content"=>array(
                            0=>[
                                "name"=>"Projected Profit and Loss",
                                "order"=>"1",
                                "content_type"=>"table",
                                "content_id"=>"5"
                            ],
                            1=>[
                                "name"=>"Projected Balance Sheet",
                                "order"=>"2",
                                "content_type"=>"table",
                                "content_id"=>"3"
                            ],
                            2=>[
                                "name"=>"Projected Cash Flow Statement",
                                "order"=>"3",
                                "content_type"=>"table",
                                "content_id"=>"4"
                            ]
                        )
                    ]

                )
            ]

        );


        return json_encode($json,true);
    }

    public static function get_topic_data()
    {
        $json=array(
            0=>[
                "name"=>"Barrier to entry",
            ],
            1=>[
                "name"=>"Company History",
            ],
            2=>[
                "name"=>"Exit Strategy",
            ],
            3=>[
                "name"=>"Expected Return",
            ],
            4=>[
                "name"=>"Future Market",
            ],
            5=>[
                "name"=>"Intellectual Property",
            ],
            6=>[
                "name"=>"Key Customers",
            ],
            7=>[
                "name"=>"Key Planned Hires",
            ],
            8=>[
                "name"=>"Market Growth",
            ],
            9=>[
                "name"=>"Market Trends",
            ],
            10=>[
                "name"=>"Company History",
            ],
            11=>[
                "name"=>"Partners And Resources",
            ],
            12=>[
                "name"=>"Pricing",
            ],
            13=>[
                "name"=>"Regular Requirements",
            ],
            14=>[
                "name"=>"Risks And Mitigations",
            ],
            15=>[
                "name"=>"Road Map",
            ]
        );
        return json_encode($json,true);
    }
}