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
                                "content_type"=>"topic",
                                "content_id"=>"1"
                            ],
                            1=>[
                                "name"=>"Solution",
                                "order"=>"2",
                                "content_type"=>"topic",
                                "content_id"=>"1"
                            ],
                            2=>[
                                "name"=>"Market",
                                "order"=>"3",
                                "content_type"=>"topic",
                                "content_id"=>"1"
                            ],
                            3=>[
                                "name"=>"Competition",
                                "order"=>"4",
                                "content_type"=>"topic",
                                "content_id"=>"1"
                            ],
                            4=>[
                                "name"=>"Why Us?",
                                "order"=>"5",
                                "content_type"=>"topic",
                                "content_id"=>"1"
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
                                "content_type"=>"topic",
                                "content_id"=>"1"
                            ],
                            1=>[
                                "name"=>"Financial Highlights by Year",
                                "order"=>"2",
                                "content_type"=>"chart",
                                "content_id"=>"1"
                            ],
                            2=>[
                                "name"=>"Financing Needed",
                                "order"=>"3",
                                "content_type"=>"topic",
                                "content_id"=>"1"
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
                                "content_type"=>"table",
                                "content_id"=>"3"
                            ],
                            1=>[
                                "name"=>"Our solution",
                                "order"=>"2",
                                "content_type"=>"chart",
                                "content_id"=>"2"
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
                                "content_type"=>"table",
                                "content_id"=>"3"
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
                                "content_type"=>"table",
                                "content_id"=>"3"
                            ],
                            1=>[
                                "name"=>"Our advantages",
                                "order"=>"2",
                                "content_type"=>"table",
                                "content_id"=>"3"
                            ]
                        )
                    ]

                )
            ]

        );


        return json_encode($json,true);
    }
}