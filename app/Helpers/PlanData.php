<?php
namespace CannaPlan\Helpers;


class PlanData
{
    public static function get_json_data()
    {
        $json=array();
        $json["chapter"]=array(
            0=>[
                "name"=>"chap 1",
                "order"=>"1",
                "section"=>array(
                    0=>[
                        "name"=>"sec 1",
                        "order"=>"1",
                        "section_content"=>array(
                            0=>[
                                "name"=>"content 1",
                                "order"=>"1",
                                "content_type"=>"chart",
                                "content_id"=>"1"
                            ],
                            1=>[
                                "name"=>"content 2",
                                "order"=>"2",
                                "content_type"=>"table",
                                "content_id"=>"1"
                            ]
                        )

                    ],
                    1=>[
                        "name"=>"sec 2",
                        "order"=>"2",
                        "section_content"=>array(
                            0=>[
                                "name"=>"content 3",
                                "order"=>"1",
                                "content_type"=>"topic",
                                "content_id"=>"1"
                            ],
                            1=>[
                                "name"=>"content 4",
                                "order"=>"2",
                                "content_type"=>"table",
                                "content_id"=>"1"
                            ]
                        )
                    ]
                    )
                ],
            1=>[
                "name"=>"chap 2",
                "order"=>"2",
                "section"=>array(
                    0=>[
                        "name"=>"sec 3",
                        "order"=>"1",
                        "section_content"=>array(
                            0=>[
                                "name"=>"content 5",
                                "order"=>"1",
                                "content_type"=>"table",
                                "content_id"=>"3"
                            ],
                            1=>[
                                "name"=>"content 6",
                                "order"=>"2",
                                "content_type"=>"chart",
                                "content_id"=>"2"
                            ]
                        )
                    ],
                    1=>[
                        "name"=>"sec 4",
                        "order"=>"2",
                        "section_content"=>array(
                            0=>[
                                "name"=>"content 7",
                                "order"=>"1",
                                "content_type"=>"table",
                                "content_id"=>"3"
                            ],
                            1=>[
                                "name"=>"content 8",
                                "order"=>"2",
                                "content_type"=>"chart",
                                "content_id"=>"2"
                            ]
                        )
                    ]

                )
            ]

        );


        return json_encode($json,true);
    }
}