$(function(){

	$(function(){
		$(".container1").mapael({
			map : {
				// Set the name of the map to display
				name : "france_departments",
			}
		});
	});

	$(".container2").mapael({
		map : {
			name : "france_departments"
			
			// Set default plots and areas style
			, defaultPlot : {
				attrs : {
					fill: "#004a9b"
					, opacity : 0.6
				}
				, attrsHover : {
					opacity : 1
				}
				, text : {
					attrs : {
						fill : "#505444"
					}
					, attrsHover : {
						fill : "#000"
					}
				}
			}
			, defaultArea: {
				attrs : {
					fill : "#f4f4e8"
					, stroke: "#ced8d0"
				}
				, attrsHover : {
					fill: "#a4e100"
				}
				, text : {
					attrs : {
						fill : "#505444"
					}
					, attrsHover : {
						fill : "#000"
					}
				}
			}
		},
		
		// Customize some areas of the map
		areas: {
			"department-56" : {
				text : {content : "Morbihan", attrs : {"font-size" : 10}}, 
				tooltip: {content : "<b>Morbihan</b> <br /> Bretagne"}
			},
			"department-21" : {
				attrs : {
					fill : "#488402"
				}
				, attrsHover : {
					fill: "#a4e100"
				}
			}
		},
		
		// Add some plots on the map
		plots : {
			// Image plot
			'paris' : {
				type : "image",
				url: "http://www.vincentbroute.fr/mapael/marker.png",
				width: 12,
				height: 40,
				latitude : 48.86, 
				longitude: 2.3444,
				attrs : {
					opacity : 1
				},
				attrsHover: {
					transform : "s1.5"
				}
			},
			// Circle plot
			'lyon' : {
				type: "circle",
				size:50,
				latitude :45.758888888889, 
				longitude: 4.8413888888889, 
				tooltip: {content : "<span style=\"font-weight:bold;\">City :</span> Lyon <br /> Rhône-Alpes"},
				text : {content : "Lyon"}
			},
			// Square plot
			'rennes' : {
				type :"square",
				size :20,
				latitude : 48.114166666667, 
				longitude: -1.6808333333333, 
				tooltip: {content : "<span style=\"font-weight:bold;\">City :</span> Rennes <br /> Bretagne"},
				text : {content : "Rennes"}
			},
			// Plot positioned by x and y instead of latitude, longitude
			'myplot' : {
				x : 300, 
				y: 200,
				text : {
					content : "My plot"
					, position: "bottom"
					, attrs : {"font-size" : 10, fill : "#004a9b", opacity: 0.6}
					, attrsHover : {fill : "#004a9b", opacity: 1}
				},
			},
			'Bordeaux' : {
				type: "circle",
				size:30,
				latitude :44.834763, 
				longitude: -0.580991,
                attrs : {
                    opacity : 1
                },
				text : {
                    content : "33",
                    position : "inner", 
                    attrs : {
						"font-size" : 16
                        , "font-weight" : "bold"
						, fill : "#fff"
					}, 
                    attrsHover : {
						"font-size" : 16
                        , "font-weight" : "bold"
						, fill : "#fff"
					}
                }
			}
		}
	});
	
	$(".container3").mapael({
		map : {
			name : "world_countries",
			defaultArea: {
				attrs : {
					fill : "#f4f4e8"
					, stroke: "#ced8d0"
				}
			}
            // Default attributes can be set for all links
            , defaultLink: {
                factor : 0.4
                , attrsHover : {
                    stroke: "#a4e100"
                }
            }
            , defaultPlot : {
                text : {
                    attrs : {
                        fill:"#000"
                    }, 
                    attrsHover : {
                        fill:"#000"
                    }
                }
            }
		},
		plots : {
			'paris' : {
				latitude :48.86, 
				longitude :2.3444, 
				tooltip: {content : "Paris<br />Population: 500000000"}
			},
			'newyork' : {
				latitude :40.667, 
				longitude :-73.833, 
				tooltip: {content : "New york<br />Population: 200001"}
			},
            'sanfrancisco' : {
				latitude: 37.792032,
				longitude: -122.394613,
				tooltip: {content : "San Francisco"}
			},
			'brasilia' : {
				latitude :-15.781682, 
				longitude :-47.924195, 
				tooltip: {content : "Brasilia<br />Population: 200000001"}
			},
			'roma': {
				latitude :41.827637, 
				longitude :12.462732, 
				tooltip: {content : "Roma"}
			},
            'miami' : {
				latitude: 25.789125,
				longitude:  -80.205674,
				tooltip: {content : "Miami"}
			},
            
            // Size=0 in order to make plots invisible
			'tokyo': {
				latitude :35.687418, 
				longitude :139.692306, 
				size:0,
                text : {content : 'Tokyo'}
			},
			'sydney' : {
				latitude :-33.917, 
				longitude :151.167,
                size:0,
                text : {content : 'Sydney'}
			},
			'plot1': {
                latitude :22.906561, 
				longitude :86.840170, 
                size:0,
                text : {content : 'Plot1', position : 'left', margin:5}
			},
			'plot2': {
                latitude :-0.390553, 
				longitude :115.586762, 
                size:0,
                text : {content : 'Plot2'}
			},
			'plot3': {
                latitude :44.065626, 
				longitude :94.576079, 
                size:0,
                text : {content : 'Plot3'}
			}
		},
        // Links allow you to connect plots between them
        links: {
            'parisnewyork' : {
                // The curve can be inverted by setting a negative factor
                factor : -0.3
                , between : ['paris', 'newyork']
                , attrs : {
                    "stroke-width" : 2
                }
                , tooltip: {content : "Paris - New-York"}
            }
            , 'parissanfrancisco' : {
                factor : -0.5
                , between : ['paris', 'sanfrancisco']
                , attrs : {
                    "stroke-width" : 4
                }
                , tooltip: {content : "Paris - San - Francisco"}
            }
            , 'parisbrasilia' : {
                factor : -0.8
                , between : ['paris', 'brasilia']
                , attrs : {
                    "stroke-width" : 1
                }
                , tooltip: {content : "Paris - Brasilia"}
            }
            , 'romamiami' : {
                factor : 0.2
                , between : ['roma', 'miami']
                , attrs : {
                    "stroke-width" : 4
                }
                , tooltip: {content : "Roma - Miami"}
            }
            , 'sydneyplot1' : {
                factor : -0.2
                , between : ['sydney', 'plot1']
                , attrs : {
                    stroke: "#a4e100",
                    "stroke-width" : 3,
                    "stroke-linecap":"round",
                    opacity:0.6
                }
                , tooltip: {content : "Sydney - Plot1"}
            }
            , 'sydneyplot2' : {
                factor : -0.1
                , between : ['sydney', 'plot2']
                , attrs : {
                    stroke: "#a4e100",
                    "stroke-width" : 8,
                    "stroke-linecap":"round",
                    opacity:0.6
                }
                , tooltip: {content : "Sydney - Plot2"}
            }
            , 'sydneyplot3' : {
                factor : 0.2
                , between : ['sydney', 'plot3']
                , attrs : {
                    stroke: "#a4e100",
                    "stroke-width" : 4,
                    "stroke-linecap":"round",
                    opacity:0.6
                }
                , tooltip: {content : "Sydney - Plot3"}
            }
            , 'sydneytokyo' : {
                factor : 0.2
                , between : ['sydney', 'tokyo']
                , attrs : {
                    stroke: "#a4e100",
                    "stroke-width" : 6,
                    "stroke-linecap":"round",
                    opacity:0.6
                }
                , tooltip: {content : "Sydney - Plot2"}
            }
        }
	});
	
	$(".container4").mapael({
		map : {
			name : "france_departments"
            // Enable zoom on the map
            , zoom : {
                enabled : true
            }
			// Set default plots and areas style
			, defaultPlot : {
				attrs : {
					fill: "#004a9b"
					, opacity : 0.6
				}
				, attrsHover : {
					opacity : 1
				}
				, text : {
					attrs : {
						fill : "#505444"
					}
					, attrsHover : {
						fill : "#000"
					}
				}
			}
			, defaultArea: {
				attrs : {
					fill : "#f4f4e8"
					, stroke: "#ced8d0"
				}
				, attrsHover : {
					fill: "#a4e100"
				}
				, text : {
					attrs : {
						fill : "#505444"
					}
					, attrsHover : {
						fill : "#000"
					}
				}
			}
		},
		
		// Customize some areas of the map
		areas: {
			"department-56" : {
				text : {content : "Morbihan", attrs : {"font-size" : 10}}, 
				tooltip: {content : "Morbihan (56)"}
			},
			"department-21" : {
				attrs : {
					fill : "#488402"
				}
				, attrsHover : {
					fill: "#a4e100"
				}
			}
		},
		
		// Add some plots on the map
		plots : {
			// Image plot
			'paris' : {
				type : "image",
				url: "http://www.vincentbroute.fr/mapael/marker.png",
				width: 12,
				height: 40,
				latitude : 48.86, 
				longitude: 2.3444,
				attrs : {
					opacity : 1
				},
				attrsHover: {
					transform : "s1.5"
				}
			},
			// Circle plot
			'lyon' : {
				type: "circle",
				size:50,
				latitude :45.758888888889, 
				longitude: 4.8413888888889, 
				value : 700000, 
				tooltip: {content : "<span style=\"font-weight:bold;\">City :</span> Lyon"},
				text : {content : "Lyon"}
			},
			// Square plot
			'rennes' : {
				type :"square",
				size :20,
				latitude : 48.114166666667, 
				longitude: -1.6808333333333, 
				tooltip: {content : "<span style=\"font-weight:bold;\">City :</span> Rennes"},
				text : {content : "Rennes"}
			},
			// Plot positioned by x and y instead of latitude, longitude
			'myplot' : {
				x : 300, 
				y: 200,
				text : {
					content : "My plot"
					, position: "bottom"
					, attrs : {"font-size" : 10, fill : "#004a9b", opacity: 0.6}
					, attrsHover : {fill : "#004a9b", opacity: 1}
				},
			}
		}
	});
	
	$(".container5").mapael({
		map : {
			name : "france_departments",
			defaultArea: {
				attrs : {
					stroke : "#fff", 
					"stroke-width" : 1
				},
				attrsHover : {
					"stroke-width" : 2
				}
			}
		},
		legend : {
			area : {
				title :"Population of France by department", 
				slices : [
					{
						max :300000, 
						attrs : {
							fill : "#97e766"
						},
						label :"Less than de 300 000 inhabitants"
					},
					{
						min :300000, 
						max :500000, 
						attrs : {
							fill : "#7fd34d"
						},
						label :"Between 100 000 and 500 000 inhabitants"
					},
					{
						min :500000, 
						max :1000000, 
						attrs : {
							fill : "#5faa32"
						},
						label :"Between 500 000 and 1 000 000 inhabitants"
					},
					{
						min :1000000, 
						attrs : {
							fill : "#3f7d1a"
						},
						label :"More than 1 million inhabitants"
					}
				]
			}
		},
		areas: {
			"department-59": {
				value: "2617939",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Nord (59)</span><br />Population : 2617939"}
			},
			"department-75": {
				value: "2268265",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Paris (75)</span><br />Population : 2268265"}
			},
			"department-13": {
				value: "2000550",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Bouches-du-Rhône (13)</span><br />Population : 2000550"}
			},
			"department-69": {
				value: "1756069",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Rhône (69)</span><br />Population : 1756069"}
			},
			"department-92": {
				value: "1590749",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Hauts-de-Seine (92)</span><br />Population : 1590749"}
			},
			"department-93": {
				value: "1534895",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Seine-Saint-Denis (93)</span><br />Population : 1534895"}
			},
			"department-62": {
				value: "1489209",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Pas-de-Calais (62)</span><br />Population : 1489209"}
			},
			"department-33": {
				value: "1479277",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Gironde (33)</span><br />Population : 1479277"}
			},
			"department-78": {
				value: "1435448",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Yvelines (78)</span><br />Population : 1435448"}
			},
			"department-77": {
				value: "1347008",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Seine-et-Marne (77)</span><br />Population : 1347008"}
			},
			"department-94": {
				value: "1340868",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Val-de-Marne (94)</span><br />Population : 1340868"}
			},
			"department-44": {
				value: "1317685",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Loire-Atlantique (44)</span><br />Population : 1317685"}
			},
			"department-76": {
				value: "1275952",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Seine-Maritime (76)</span><br />Population : 1275952"}
			},
			"department-31": {
				value: "1268370",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Haute-Garonne (31)</span><br />Population : 1268370"}
			},
			"department-38": {
				value: "1233759",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Isère (38)</span><br />Population : 1233759"}
			},
			"department-91": {
				value: "1233645",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Essonne (91)</span><br />Population : 1233645"}
			},
			"department-95": {
				value: "1187836",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Val-d'Oise (95)</span><br />Population : 1187836"}
			},
			"department-67": {
				value: "1115226",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Bas-Rhin (67)</span><br />Population : 1115226"}
			},
			"department-06": {
				value: "1094579",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Alpes-Maritimes (06)</span><br />Population : 1094579"}
			},
			"department-57": {
				value: "1066667",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Moselle (57)</span><br />Population : 1066667"}
			},
			"department-34": {
				value: "1062617",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Hérault (34)</span><br />Population : 1062617"}
			},
			"department-83": {
				value: "1026222",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Var (83)</span><br />Population : 1026222"}
			},
			"department-35": {
				value: "1015470",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Ille-et-Vilaine (35)</span><br />Population : 1015470"}
			},
			"department-29": {
				value: "929286",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Finistère (29)</span><br />Population : 929286"}
			},
			"department-974": {
				value: "829903",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">La Réunion (974)</span><br />Population : 829903"}
			},
			"department-60": {
				value: "823668",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Oise (60)</span><br />Population : 823668"}
			},
			"department-49": {
				value: "808298",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Maine-et-Loire (49)</span><br />Population : 808298"}
			},
			"department-42": {
				value: "766729",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Loire (42)</span><br />Population : 766729"}
			},
			"department-68": {
				value: "765634",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Haut-Rhin (68)</span><br />Population : 765634"}
			},
			"department-74": {
				value: "760979",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Haute-Savoie (74)</span><br />Population : 760979"}
			},
			"department-54": {
				value: "746502",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Meurthe-et-Moselle (54)</span><br />Population : 746502"}
			},
			"department-56": {
				value: "744663",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Morbihan (56)</span><br />Population : 744663"}
			},
			"department-30": {
				value: "726285",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Gard (30)</span><br />Population : 726285"}
			},
			"department-14": {
				value: "699561",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Calvados (14)</span><br />Population : 699561"}
			},
			"department-45": {
				value: "674913",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Loiret (45)</span><br />Population : 674913"}
			},
			"department-64": {
				value: "674908",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Pyrénées-Atlantiques (64)</span><br />Population : 674908"}
			},
			"department-85": {
				value: "654096",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Vendée (85)</span><br />Population : 654096"}
			},
			"department-63": {
				value: "649643",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Puy-de-Dôme (63)</span><br />Population : 649643"}
			},
			"department-17": {
				value: "640803",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Charente-Maritime (17)</span><br />Population : 640803"}
			},
			"department-01": {
				value: "614331",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Ain (01)</span><br />Population : 614331"}
			},
			"department-22": {
				value: "612383",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Côtes-d'Armor (22)</span><br />Population : 612383"}
			},
			"department-37": {
				value: "605819",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Indre-et-Loire (37)</span><br />Population : 605819"}
			},
			"department-27": {
				value: "603194",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Eure (27)</span><br />Population : 603194"}
			},
			"department-80": {
				value: "583388",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Somme (80)</span><br />Population : 583388"}
			},
			"department-51": {
				value: "579533",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Marne (51)</span><br />Population : 579533"}
			},
			"department-72": {
				value: "579497",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Sarthe (72)</span><br />Population : 579497"}
			},
			"department-71": {
				value: "574874",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Saône-et-Loire (71)</span><br />Population : 574874"}
			},
			"department-84": {
				value: "555240",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Vaucluse (84)</span><br />Population : 555240"}
			},
			"department-02": {
				value: "555094",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Aisne (02)</span><br />Population : 555094"}
			},
			"department-25": {
				value: "542509",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Doubs (25)</span><br />Population : 542509"}
			},
			"department-21": {
				value: "538505",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Côte-d'Or (21)</span><br />Population : 538505"}
			},
			"department-50": {
				value: "517121",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Manche (50)</span><br />Population : 517121"}
			},
			"department-26": {
				value: "499313",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Drôme (26)</span><br />Population : 499313"}
			},
			"department-66": {
				value: "457238",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Pyrénées-Orientales (66)</span><br />Population : 457238"}
			},
			"department-28": {
				value: "440291",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Eure-et-Loir (28)</span><br />Population : 440291"}
			},
			"department-86": {
				value: "438566",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Vienne (86)</span><br />Population : 438566"}
			},
			"department-73": {
				value: "428751",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Savoie (73)</span><br />Population : 428751"}
			},
			"department-24": {
				value: "426607",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Dordogne (24)</span><br />Population : 426607"}
			},
			"department-971": {
				value: "409905",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Guadeloupe (971)</span><br />Population : 409905"}
			},
			"department-972": {
				value: "400535",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Martinique (972)</span><br />Population : 400535"}
			},
			"department-40": {
				value: "397766",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Landes (40)</span><br />Population : 397766"}
			},
			"department-88": {
				value: "392846",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Vosges (88)</span><br />Population : 392846"}
			},
			"department-81": {
				value: "387099",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Tarn (81)</span><br />Population : 387099"}
			},
			"department-87": {
				value: "384781",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Haute-Vienne (87)</span><br />Population : 384781"}
			},
			"department-79": {
				value: "380569",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Deux-Sèvres (79)</span><br />Population : 380569"}
			},
			"department-11": {
				value: "365854",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Aude (11)</span><br />Population : 365854"}
			},
			"department-16": {
				value: "364429",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Charente (16)</span><br />Population : 364429"}
			},
			"department-89": {
				value: "353366",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Yonne (89)</span><br />Population : 353366"}
			},
			"department-03": {
				value: "353124",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Allier (03)</span><br />Population : 353124"}
			},
			"department-47": {
				value: "342500",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Lot-et-Garonne (47)</span><br />Population : 342500"}
			},
			"department-41": {
				value: "340729",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Loir-et-Cher (41)</span><br />Population : 340729"}
			},
			"department-07": {
				value: "324885",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Ardèche (07)</span><br />Population : 324885"}
			},
			"department-18": {
				value: "319600",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Cher (18)</span><br />Population : 319600"}
			},
			"department-53": {
				value: "317006",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Mayenne (53)</span><br />Population : 317006"}
			},
			"department-10": {
				value: "311720",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Aube (10)</span><br />Population : 311720"}
			},
			"department-61": {
				value: "301421",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Orne (61)</span><br />Population : 301421"}
			},
			"department-08": {
				value: "291678",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Ardennes (08)</span><br />Population : 291678"}
			},
			"department-12": {
				value: "288364",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Aveyron (12)</span><br />Population : 288364"}
			},
			"department-39": {
				value: "271973",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Jura (39)</span><br />Population : 271973"}
			},
			"department-19": {
				value: "252235",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Corrèze (19)</span><br />Population : 252235"}
			},
			"department-82": {
				value: "248227",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Tarn-et-Garonne (82)</span><br />Population : 248227"}
			},
			"department-70": {
				value: "247311",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Haute-Saône (70)</span><br />Population : 247311"}
			},
			"department-36": {
				value: "238261",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Indre (36)</span><br />Population : 238261"}
			},
			"department-65": {
				value: "237945",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Hautes-Pyrénées (65)</span><br />Population : 237945"}
			},
			"department-43": {
				value: "231877",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Haute-Loire (43)</span><br />Population : 231877"}
			},
			"department-973": {
				value: "231167",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Guyane (973)</span><br />Population : 231167"}
			},
			"department-58": {
				value: "226997",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Nièvre (58)</span><br />Population : 226997"}
			},
			"department-55": {
				value: "200509",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Meuse (55)</span><br />Population : 200509"}
			},
			"department-32": {
				value: "195489",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Gers (32)</span><br />Population : 195489"}
			},
			"department-52": {
				value: "191004",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Haute-Marne (52)</span><br />Population : 191004"}
			},
			"department-46": {
				value: "181232",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Lot (46)</span><br />Population : 181232"}
			},
			"department-2B": {
				value: "168869",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Haute-Corse (2B)</span><br />Population : 168869"}
			},
			"department-04": {
				value: "165155",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Alpes-de-Haute-Provence (04)</span><br />Population : 165155"}
			},
			"department-09": {
				value: "157582",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Ariège (09)</span><br />Population : 157582"}
			},
			"department-15": {
				value: "154135",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Cantal (15)</span><br />Population : 154135"}
			},
			"department-90": {
				value: "146475",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Territoire de Belfort (90)</span><br />Population : 146475"}
			},
			"department-2A": {
				value: "145998",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Corse-du-Sud (2A)</span><br />Population : 145998"}
			},
			"department-05": {
				value: "142312",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Hautes-Alpes (05)</span><br />Population : 142312"}
			},
			"department-23": {
				value: "127919",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Creuse (23)</span><br />Population : 127919"}
			},
			"department-48": {
				value: "81281",
				href : "#",
				tooltip: {content : "<span style=\"font-weight:bold;\">Lozère (48)</span><br />Population : 81281"}
			}
		}
	});
	
});