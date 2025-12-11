const bancosMX = {
  "instituciones": [
      "ACTINVER",
      "AFIRME",
      "albo",
      "ARCUS FI",
      "ASP INTEGRA OPC",
      "AUTOFIN",
      "AZTECA",
      "BaBien",
      "BAJIO",
      "BANAMEX",
      "BANCO COVALTO",
      "BANCOMEXT",
      "BANCOPPEL",
      "BANCO S3",
      "BANCREA",
      "BANJERCITO",
      "BANKAOOL",
      "BANK OF AMERICA",
      "BANK OF CHINA",
      "BANOBRAS",
      "BANORTE",
      "BANREGIO",
      "BANSI",
      "BANXICO",
      "BARCLAYS",
      "BBASE",
      "Banca Afirme",
      "Banca Mifel",
      "Banco Actinver",
      "Banco Azteca",
      "Banco Bancrea",
      "Banco Base",
      "Banco Bineo",
      "Banco Citi México",
      "Banco Compartamos",
      "Banco Covalto",
      "Banco Credit Suisse (México)",
      "Banco del Bajío",
      "Banco de Inversión Afirme",
      "Banco del Bajío",
      "Banco Finterra",
      "Banco Forjadores",
      "Banco Inbursa",
      "Banco Inmobiliario Mexicano",
      "Banco Invex",
      "Banco JP Morgan",
      "Banco KEB Hana México",
      "Banco Monex",
      "Banco Multiva",
      "Banco Nacional de México",
      "Banco PagaTodo",
      "Banco Plata",
      "Banco Regional de Monterrey",
      "Banco S3 Caceis México",
      "Banco Sabadell",
      "Banco Santander",
      "Banco Shinhan de México",
      "Banco Ve por Más",
      "BanCoppel",
      "Bank of America Mexico",
      "Bank of China Mexico",
      "Bankaool",
      "Banorte",
      "Bansí",
      "Barclays Bank México",
      "BBVA México",
      "BNP Paribas",
      "CIBanco",
      "Consubanco",
      "Fundación Dondé Banco",
      "Hey Banco",
      "HSBC México",
      "Industrial and Commercial Bank of China México",
      "Intercam Banco",
      "Kapital Bank",
      "Mizuho Bank",
      "MUFG Bank Mexico",
      "Nu México",
      "Openbank",
      "Revolut",
      "Scotiabank",
      "UALÁ",
      "Otros"
  ]
};

const regimenesMX = {
  "regimenes": [
    "601 - General de Ley Personas Morales",
    "603 - Personas Morales con Fines no Lucrativos",
    "605 - Sueldos y Salarios e Ingresos Asimilados a Salarios",
    "606 - Arrendamiento",
    "607 - Régimen de Enajenación o Adquisición de Bienes",
    "608 - Demás ingresos",
    "609 - Consolidación",
    "610 - Residentes en el Extranjero sin Establecimiento Permanente en México",
    "611 - Ingresos por Dividendos (socios y accionistas)",
    "612 - Personas Físicas con Actividades Empresariales y Profesionales",
    "614 - Ingresos por intereses",
    "615 - Régimen de los ingresos por obtención de premios",
    "616 - Sin obligaciones fiscales",
    "620 - Sociedades Cooperativas de Producción que optan por diferir sus ingresos",
    "621 - Incorporación Fiscal (RIF)",
    "622 - Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras",
    "623 - Opcional para Grupos de Sociedades",
    "624 - Coordinados",
    "625 - Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas",
    "626 - Régimen Simplificado de Confianza (RESICO)",
    "628 - Régimen de Hidrocarburos",
    "629 - Regímenes Fiscales Preferentes y Empresas Multinacionales",
    "630 - Enajenación de acciones en bolsa de valores"
  ]
};

const estadosMunicipiosMX = {
  "estados": [
    "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Coahuila",
    "Colima", "Chiapas", "Chihuahua", "Ciudad de México", "Durango", "Guanajuato",
    "Guerrero", "Hidalgo", "Jalisco", "México", "Michoacán", "Morelos", "Nayarit",
    "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí",
    "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas"
  ],
  "municipios": {
  "Aguascalientes": ["Aguascalientes","Asientos","Calvillo","Cosío","Jesús María","Pabellón de Arteaga","Rincón de Romos","San José de Gracia","Tepezalá","El Llano"],
  "Baja California": ["Ensenada","Mexicali","Tecate","Tijuana","Playas de Rosarito"],
  "Baja California Sur": ["Comondú","La Paz","Loreto","Los Cabos","Mulegé"],
  "Campeche": ["Calakmul","Calkiní","Campeche","Carmen","Champotón","Escárcega","Hecelchakán","Hopelchén","Palizada","Tenabo"],
  "Coahuila": ["Abasolo","Acuña","Allende","Arteaga","Candela","Castaños","Cuatro Ciénegas","Frontera","General Cepeda","Guerrero","Hidalgo","Jiménez","Juárez","Lamadrid","Matamoros","Monclova","Morelos","Múzquiz","Nadadores","Nava","Ocampo","Parras","Piedras Negras","Progreso","Ramos Arizpe","Sabinas","Sacramento","Saltillo","San Buenaventura","San Juan de Sabinas","San Pedro","Sierra Mojada","Torreón","Viesca","Villa Unión","Zaragoza"],
  "Colima": ["Armería","Colima","Comala","Coquimatlán","Cuauhtémoc","Ixtlahuacán","Manzanillo","Minatitlán","Tecomán","Villa de Álvarez"],
  "Chiapas": ["Acacoyagua","Acala","Acapetahua","Altamirano","Amatán","Amatenango de la Frontera","Amatenango del Valle","Angel Albino Corzo","Arriaga","Bejucal de Ocampo","Bella Vista","Benemérito de las Américas","Berriozábal","Bochil","Cacahoatán","Catazajá","Chalchihuitán","Chamula","Chanal","Chapultenango","Chenalhó","Chiapa de Corzo","Chiapilla","Chicoasén","Chicomuselo","Chilón","Cintalapa","Coapilla","Comitán de Domínguez","La Concordia","Copainalá","El Bosque","El Porvenir","Escuintla","Francisco León","Frontera Comalapa","Frontera Hidalgo","Huehuetán","Huitiupán","Huixtla","Huixtán","La Independencia","Ixhuatán","Ixtacomitán","Ixtapa","Ixtapangajoya","Jiquipilas","Jitotol","Juárez","Larráinzar","Las Margaritas","Las Rosas","Mapastepec","Maravilla Tenejapa","Marqués de Comillas","Mazapa de Madero","Mazatán","Metapa","Mitontic","Motozintla","Nicolás Ruíz","Ocosingo","Ocotepec","Ocozocoautla de Espinosa","Osumacinta","Oxchuc","Palenque","Pantelhó","Pantepec","Pichucalco","Pijijiapan","El Parral","Pueblo Nuevo Solistahuacán","Rayón","Reforma","Sabanilla","Salto de Agua","San Cristóbal de las Casas","San Fernando","Siltepec","Simojovel","Sitalá","Socoltenango","Solosuchiapa","Soyaló","Suchiapa","Suchiate","Sunuapa","Tapachula","Tapalapa","Tapilula","Tecpatán","Tenejapa","Teopisca","Tila","Tumbalá","Tuxtla Gutiérrez","Tuxtla Chico","Tuzantán","Tzimol","Union Juárez","Venustiano Carranza","Villa Comaltitlán","Villaflores","Yajalón","San Juan Cancuc","Aldama","Benemérito de Chiapas","La Trinitaria","Palestina de los Altos","Tonalá"],
  "Chihuahua": ["Ahumada","Aldama","Allende","Aquiles Serdán","Ascensión","Bachíniva","Balleza","Batopilas","Bocoyna","Buenaventura","Camargo","Carichí","Casas Grandes","Chihuahua","Chínipas","Coronado","Coyame del Sotol","Cuauhtémoc","Cusihuiriachi","Dr. Belisario Domínguez","Galeana","Gómez Farías","Gran Morelos","Guachochi","Guadalupe","Guadalupe y Calvo","Guazapares","Guerrero","Hidalgo del Parral","Huejotitán","Ignacio Zaragoza","Janos","Jiménez","Julimes","López","Madera","Maguarichi","Manuel Benavides","Matachí","Matamoros","Meoqui","Morelos","Moris","Namiquipa","Nonoava","Nuevo Casas Grandes","Ocampo","Ojinaga","Praxedis G. Guerrero","Riva Palacio","Rosales","Rosario","San Francisco de Borja","San Francisco de Conchos","San Francisco del Oro","Santa Bárbara","Satevó","Saucillo","Temósachic","Urique","Uruachi","Valle de Zaragoza"],
  "Ciudad de México": ["Álvaro Obregón","Azcapotzalco","Benito Juárez","Coyoacán","Cuajimalpa de Morelos","Cuauhtémoc","Gustavo A. Madero","Iztacalco","Iztapalapa","La Magdalena Contreras","Miguel Hidalgo","Milpa Alta","Tláhuac","Tlalpan","Venustiano Carranza","Xochimilco"],
  "Durango": ["Canatlán","Canelas","Coneto de Comonfort","Cuencamé","Durango","General Simón Bolívar","Gómez Palacio","Guadalupe Victoria","Guanaceví","Hidalgo","Indé","Lerdo","Mapimí","Mezquital","Nombre de Dios","Ocampo","El Oro","Otáez","Pánuco de Coronado","Peñón Blanco","Poanas","Pueblo Nuevo","Rodeo","San Bernardo","San Dimas","San Juan de Guadalupe","San Juan del Río","San Luis del Cordero","San Pedro del Gallo","Santa Clara","Santiago Papasquiaro","Súchil","Tamazula","Tepehuanes","Tlahualilo","Topia","Vicente Guerrero"],
  "Guanajuato": ["Abasolo","Acámbaro","San Miguel de Allende","Apaseo el Alto","Apaseo el Grande","Atarjea","Celaya","Manuel Doblado","Comonfort","Coroneo","Cortazar","Cuerámaro","Doctor Mora","Dolores Hidalgo Cuna de la Independencia Nacional","Guanajuato","Huanímaro","Irapuato","Jaral del Progreso","Jerécuaro","León","Moroleón","Ocampo","Pénjamo","Pueblo Nuevo","Purísima del Rincón","Romita","Salamanca","Salvatierra","San Diego de la Unión","San Felipe","San Francisco del Rincón","San José Iturbide","San Luis de la Paz","Santa Catarina","Santa Cruz de Juventino Rosas","Santiago Maravatío","Silao de la Victoria","Tarandacuao","Tarimoro","Tierra Blanca","Uriangato","Valle de Santiago","Victoria","Villagrán","Xichú","Yuriria"],
  "Guerrero": ["Acapulco de Juárez","Ahuacuotzingo","Ajuchitlán del Progreso","Alcozauca de Guerrero","Alpoyeca","Apaxtla","Arcelia","Atenango del Río","Atlamajalcingo del Monte","Atlixtac","Azoyú","Benito Juárez","Buenavista de Cuéllar","Chilapa de Álvarez","Chilpancingo de los Bravo","Coahuayutla de José María Izazaga","Cocula","Copala","Copalillo","Copanatoyac","Coyuca de Benítez","Coyuca de Catalán","Cuajinicuilapa","Cualác","Cuautepec","Cuetzala del Progreso","Cutzamala de Pinzón","Eduardo Neri","Florencio Villarreal","General Canuto A. Neri","General Heliodoro Castillo","Huamuxtitlán","Huitzuco de los Figueroa","Iguala de la Independencia","Igualapa","Ixcateopan de Cuauhtémoc","Zihuatanejo de Azueta","Juan R. Escudero","Leonardo Bravo","Malinaltepec","Mártir de Cuilapan","Metlatónoc","Mochitlán","Olinalá","Ometepec","Pedro Ascencio Alquisiras","Petatlán","Pilcaya","Pungarabato","Quechultenango","San Luis Acatlán","San Marcos","San Miguel Totolapan","Taxco de Alarcón","Tecoanapa","Tecpan de Galeana","Teloloapan","Tepecoacuilco de Trujano","Tetipac","Tixtla de Guerrero","Tlacoachistlahuaca","Tlacoapa","Tlalchapa","Tlalixtaquilla de Maldonado","Tlapa de Comonfort","Tlapehuala","La Unión de Isidoro Montes de Oca","Xalpatláhuac","Xochihuehuetlán","Zapotitlán Tablas","Zirándaro","Zitlala"],
  "Hidalgo": ["Actopan","Agua Blanca de Iturbide","Ajacuba","Alfajayucan","Almoloya","Apan","Atitalaquia","Atlapexco","Atotonilco de Tula","Atotonilco el Grande","Calnali","Cardonal","Chapantongo","Chapulhuacán","Ciudad de Huichapan","Cuautepec de Hinojosa","El Arenal","El Cardonal","Epazoyucan","Francisco I. Madero","Huasca de Ocampo","Huautla","Huehuetla","Huejutla de Reyes","Huichapan","Ixmiquilpan","Jacala de Ledezma","Jaltocán","Juárez Hidalgo","Lolotla","Metepec","San Agustín Metzquititlán","San Bartolo Tutotepec","San Salvador","Santiago de Anaya","Santiago Tulantepec de Lugo Guerrero","Singuilucan","Tasquillo","Tetepango","Villa de Tezontepec","Tenango de Doria","Tepeapulco","Tepehuacán de Guerrero","Tepeji del Río de Ocampo","Tepetitlán","Tetlahueca","Tlanchinol","Tlaxcoapan","Tolcayuca","Tula de Allende","Tulancingo de Bravo","Xochicoatlán","Yahualica","Zacualtipán de Ángeles","Zapotlán de Juárez","Zempoala"],
  "Jalisco": ["Acatic","Acatlán de Juárez","Ahualulco de Mercado","Amacueca","Amatitán","Ameca","San Juanito de Escobedo","Arandas","El Arenal","Atemajac de Brizuela","Atengo","Atenguillo","Atotonilco el Alto","Atoyac","Autlán de Navarro","Ayotlán","Ayutla","La Barca","Bolaños","Cabo Corrientes","Casimiro Castillo","Cihuatlán","Zapotlán el Grande","Cocula","Colotlán","Concepción de Buenos Aires","Cuautitlán de García Barragán","Cuautla","Cuquío","Chapala","Chimaltitán","Chiquilistlán","Degollado","Ejutla","Encarnación de Díaz","Etzatlán","El Grullo","Guachinango","Guadalajara","Hostotipaquillo","Huejúcar","La Huerta","Ixtlahuacán de los Membrillos","Ixtlahuacán del Río","Jalostotitlán","Jamay","Jesús María","Jilotlán de los Dolores","Jocotepec","Juanacatlán","Juchitlán","Lagos de Moreno","El Limón","Magdalena","Santa María del Oro","La Manzanilla de la Paz","Mascota","Mazamitla","Mexticacán","Mezquitic","Mixtlán","Ocotlán","Ojuelos de Jalisco","Pihuamo","Poncitlán","Puerto Vallarta","Villa Purificación","Quitupan","El Salto","San Cristóbal de la Barranca","San Diego de Alejandría","San Juan de los Lagos","San Julián","San Marcos","San Martín de Bolaños","San Martín Hidalgo","San Miguel el Alto","Gómez Farías","San Sebastián del Oeste","Santa María de los Ángeles","Sayula","Tala","Talpa de Allende","Tamazula de Gordiano","Tapalpa","Tecalitlán","Tecolotlán","Tenamaxtlán","Teocaltiche","Teocuitatlán de Corona","Tepatitlán de Morelos","Tequila","Teuchitlán","Tizapán el Alto","Tlajomulco de Zúñiga","Tlaquepaque","Tolimán","Tomatlán","Tonaya","Tonila","Totatiche","Tototlán","Tuxcacuesco","Tuxcueca","Tuxpan","Unión de San Antonio","Unión de Tula","Valle de Guadalupe","Valle de Juárez","San Gabriel","Zacoalco de Torres","Zapopan","Zapotiltic","Zapotitlán de Vadillo","Zapotlán del Rey","Zapotlanejo"],
  "México": ["Acambay de Ruíz Castañeda","Acolman","Aculco","Almoloya de Alquisiras","Almoloya de Juárez","Almoloya del Río","Amanalco","Amatepec","Amecameca","Apaxco","Atenco","Atizapán","Atizapán de Zaragoza","Atlacomulco","Atlautla","Axapusco","Ayapango","Calimaya","Capulhuac","Chalco","Chapa de Mota","Chapultepec","Chiautla","Chicoloapan","Chiconcuac","Chimalhuacán","Coacalco de Berriozábal","Cocotitlán","Coyotepec","Cuautitlán","Cuautitlán Izcalli","Donato Guerra","Ecatepec de Morelos","Ecatzingo","Huehuetoca","Hueypoxtla","Huixquilucan","Isidro Fabela","Ixtapaluca","Ixtapan de la Sal","Ixtapan del Oro","Ixtlahuaca","Xalatlaco","Jaltenco","Jilotepec","Jilotzingo","Jiquipilco","Jocotitlán","Joquicingo","Juchitepec","Lerma","Malinalco","Melchor Ocampo","Metepec","Mexicaltzingo","Morelos","Naucalpan de Juárez","Nezahualcóyotl","Nextlalpan","Nicolás Romero","Nopaltepec","Ocoyoacac","Ocuilan","El Oro","Otumba","Otzoloapan","Otzolotepec","Ozumba","Papalotla","La Paz","Polotitlán","Rayón","San Antonio la Isla","San Felipe del Progreso","San Martín de las Pirámides","San Mateo Atenco","San Simón de Guerrero","Santo Tomás","Soyaniquilpan de Juárez","Sultepec","Tecámac","Tejupilco","Temamatla","Temascalapa","Temascalcingo","Temascaltepec","Temoaya","Tenancingo","Tenango del Aire","Tenango del Valle","Teoloyucan","Teotihuacán","Tepetlaoxtoc","Tepetlixpa","Tepotzotlán","Tequixquiac","Texcaltitlán","Texcalyacac","Texcoco","Tezoyuca","Tianguistenco","Timilpan","Tlalmanalco","Tlalnepantla de Baz","Tlatlaya","Toluca","Tonanitla","Tonatico","Tultepec","Tultitlán","Valle de Bravo","Valle de Chalco Solidaridad","Villa de Allende","Villa del Carbón","Villa Guerrero","Villa Victoria","Xonacatlán","Zacazonapan","Zacualpan","Zinacantepec","Zumpahuacán","Zumpango"],
  "Michoacán": ["Acuitzio","Aguililla","Álvaro Obregón","Angamacutiro","Angangueo","Apatzingán","Aporo","Aquila","Ario","Arteaga","Briseñas","Buenavista","Carácuaro","Charapan","Charo","Chavinda","Cherán","Chilchota","Chinicuila","Chucándiro","Churintzio","Churumuco","Ecuandureo","Epitacio Huerta","Erongarícuaro","Gabriel Zamora","Hidalgo","Huandacareo","Huaniqueo","Huetamo","Huiramba","Indaparapeo","Irimbo","Ixtlán","Jacona","Jiménez","Jiquilpan","Juárez","Jungapeo","Lagunillas","Madero","Maravatío","Marcos Castellanos","Lázaro Cárdenas","Morelia","Morelos","Múgica","Nahuatzen","Nocupétaro","Nuevo Parangaricutiro","Nuevo Urecho","Numarán","Ocampo","Pajacuarán","Panindícuaro","Parácuaro","Pátzcuaro","Penjamillo","Peribán","La Piedad","Purepero","Puruándiro","Queréndaro","Quiroga","Cojumatlán de Régules","Los Reyes","Sahuayo","San Lucas","Santa Ana Maya","Senguio","Susupuato","Tacámbaro","Tancítaro","Tangamandapio","Tangancícuaro","Tanhuato","Taretan","Tarímbaro","Tepalcatepec","Tingambato","Tingüindín","Tiquicheo de Nicolás Romero","Tlalpujahua","Tlazazalca","Tocumbo","Tumbiscatío","Turicato","Tuxpan","Tuzantla","Tzintzuntzan","Tzitzio","Uruapan","Venustiano Carranza","Villamar","Vista Hermosa","Yurécuaro","Zacapu","Zamora","Zináparo","Zinapécuaro","Ziracuaretiro","Zitácuaro"],
  "Morelos": ["Amacuzac","Atlatlahucan","Axochiapan","Ayala","Coatlán del Río","Cuautla","Cuernavaca","Emiliano Zapata","Huitzilac","Jantetelco","Jiutepec","Jojutla","Jonacatepec","Mazatepec","Miacatlán","Ocuituco","Puente de Ixtla","Temixco","Temoac","Tepalcingo","Tepoztlán","Tetecala","Tetela del Volcán","Tlaltizapán","Tlaquiltenango","Tlaxcoapan","Tomatlán","Tlayacapan","Totolapan","Xochitepec","Yautepec","Yecapixtla","Zacatepec","Zacualpan de Amilpas","Zapotlán del Río","Zautla"],
  "Nayarit": ["Ahuacatlán","Amatlán de Cañas","Bahía de Banderas","Compostela","Del Nayar","Huajicori","Ixtlán del Río","Jala","San Blas","San Pedro Lagunillas","Santa María del Oro","Santiago Ixcuintla","Tecuala","Tepic","Tuxpan","La Yesca"],
  "Nuevo León": ["Abasolo","Agualeguas","Los Aldamas","Allende","Anáhuac","Apodaca","Aramberri","Bustamante","Cadereyta Jiménez","El Carmen","Cerralvo","China","Doctor Arroyo","Galeana","García","San Pedro Garza García","General Bravo","General Escobedo","General Terán","General Treviño","General Zaragoza","Guadalupe","Los Herreras","Higueras","Hualahuises","Iturbide","Juárez","Lampazos de Naranjo","Linares","Marín","Melchor Ocampo","Mier y Noriega","Mina","Montemorelos","Monterrey","Parás","Pesquería","Los Ramones","Rayones","Sabinas Hidalgo","Salinas Victoria","San Nicolás de los Garza","Cienega de Flores","San Pedro","Santa Catarina","Santiago","Vallecillo","Villaldama"],
  "Oaxaca": ["Abejones","Ánimas Trujano","Asunción Cacalotepec","Asunción Cuyotepeji","Asunción Ixtaltepec","Asunción Nochixtlán","Asunción Ocotlán","Asunción Tlacolulita","Ayotzintepec","Ciudad Ixtepec","Cuicatlán","Chahuites","Chalcatongo de Hidalgo","Candelaria Loxicha","Ciénega de Zimatlán","Coatecas Altas","Coicoyán de las Flores","Colonia Hidalgo","Cosolapa","Cuilápam de Guerrero","Cuyamecalco Villa de Zaragoza","El Barrio de la Soledad","El Espinal","Eloxochitlán de Flores Magón","Fresnillo de Trujano","Guadalupe Etla","Guadalupe de Ramírez","Guelatao de Juárez","Guevea de Humboldt","Heroica Ciudad de Ejutla de Crespo","Heroica Ciudad de Huajuapan de León","Heroica Ciudad de Tlaxiaco","Huautla de Jiménez","Ixtlán de Juárez","Juchitán de Zaragoza","La Compañía","La Pe","Lagunas","Loma Bonita","Magdalena Apasco","Magdalena Jaltepec","Magdalena Mixtepec","Magdalena Ocotlán","Magdalena Peñasco","Magdalena Tequisistlán","Mariscala de Juárez","Mártires de Tacubaya","Matías Romero Avendaño","Mazatlán Villa de Flores","Miahuatlán de Porfirio Díaz","Mixistlán de la Reforma","Monjas","Natividad","Nazareno Etla","Nejapa de Madero","Nuevo Zoquiápam","Oaxaca de Juárez","Ocotlán de Morelos","Palo Verde","Petapa","Piedras Negras","Pinotepa de Don Luis","Pluma Hidalgo","Putla Villa de Guerrero","Reforma de Pineda","Reyes Etla","Rojas de Cuauhtémoc","Salina Cruz","San Agustín Amatengo","San Agustín Atenango","San Agustín Chayuco","San Agustín Loxicha","San Agustín Tlacotepec","San Agustín Yatareni","San Andrés Cabecera Nueva","San Andrés Dinicuiti","San Andrés Huaxpaltepec","San Andrés Huayápam","San Andrés Ixtlahuaca","San Andrés Lagunas","San Andrés Nuxiño","San Andrés Paxtlán","San Andrés Sinaxtla","San Andrés Solaga","San Andrés Teotilalpam","San Andrés Tepetlapa","San Andrés Yaá","San Andrés Yutatío","San Lorenzo Albarradas","San Lorenzo Cacaotepec","San Lorenzo Cuaunecuiltitla","San Lorenzo Texmelúcan","San Lucas Camotlán","San Lucas Ojitlán","San Lucas Quiaviní","San Lucas Zoquiápam","San Luis Amatlán","San Marcial Ozolotepec","San Marcos Arteaga","San Martín de los Cansecos","San Martín Huamelúlpam","San Martín Itunyoso","San Martín Lachilá","San Martín Peras","San Martín Tilcajete","San Martín Toxpalan","San Martín Zacatepec","San Mateo Cajonos","San Mateo del Mar","San Mateo Etlatongo","San Mateo Nejápam","San Mateo Peñasco","San Mateo Piñas","San Mateo Río Hondo","San Mateo Sindihui","San Mateo Yoloxochitlán","San Mateo Yucutindoo","San Melchor Betaza","San Miguel Achiutla","San Miguel Ahuehuetitlán","San Miguel Aloápam","San Miguel Amatitlán","San Miguel Amatlán","San Miguel Coatlán","San Miguel Chicahua","San Miguel Chimalapa","San Miguel del Puerto","San Miguel el Grande","San Miguel Huautla","San Miguel Mixtepec","San Miguel Panixtlahuaca","San Miguel Peras","San Miguel Piedras","San Miguel Quetzaltepec","San Miguel Santa Flor","San Miguel Soyaltepec","San Miguel Suchixtepec","San Miguel Tecomatlán","San Miguel Tenango","San Miguel Tlacamama","San Miguel Tlalixtahua","San Miguel Topilejo","San Miguel Totolapan","San Miguel Tulancingo","San Miguel Yotao","San Nicolás","San Pablo Coatlán","San Pablo Cuatro Venados","San Pablo Etla","San Pablo Huitzo","San Pablo Huixtepec","San Pablo Macuiltianguis","San Pablo Tijaltepec","San Pablo Villa de Mitla","San Pablo Yaganiza","San Pedro Amuzgos","San Pedro Apóstol","San Pedro Atoyac","San Pedro Cajonos","San Pedro Coxcaltepec Cántaros","San Pedro Huamelula","San Pedro Huilotepec","San Pedro Ixcatlán","San Pedro Ixtlahuaca","San Pedro Jaltepetongo","San Pedro Jocotipac","San Pedro Juchatengo","San Pedro Mártir","San Pedro Mártir Quiechapa","San Pedro Mártir Yucuxaco","San Pedro Mixtepec Distrito 22","San Pedro Mixtepec Distrito 26","San Pedro Pochutla","San Pedro Quiatoni","San Pedro Sochiápam","San Pedro Tapanatepec","San Pedro Teozacoalco","San Pedro Teutila","San Pedro Tidaá","San Pedro Topiltepec","San Pedro Yaneri","San Pedro Yólox","San Pedro y San Pablo Ayutla","San Pedro y San Pablo Teposcolula","San Pedro y San Pablo Tequixtepec","San Pedro Yucunama","San Raymundo Jalpan","San Sebastián Abasolo","San Sebastián Coatán","San Sebastián Ixcapa","San Sebastián Nicananduta","San Sebastián Río Hondo","San Sebastián Tecomaxtlahuaca","San Sebastián Teitipac","San Sebastián Tutla","Santa Ana","Santa Ana Ateixtlahuaca","Santa Ana Cuauhtémoc","Santa Ana del Valle","Santa Ana Tavela","Santa Ana Tlacotenco","Santa Ana Yareni","Santa Ana Zegache","Santa Catalina Quierí","Santa Catarina Cuixtla","Santa Catarina Ixtepeji","Santa Catarina Juquila","Santa Catarina Lachatao","Santa Catarina Loxicha","Santa Catarina Mechoacán","Santa Catarina Minas","Santa Catarina Quiané","Santa Catarina Quioquitani","Santa Catarina Salinas","Santa Catarina Tayata","Santa Catarina Yosonotú","Santa Catarina Zapoquila","Santa Cruz Acatepec","Santa Cruz Amilpas","Santa Cruz de Bravo","Santa Cruz Itundujia","Santa Cruz Mixtepec","Santa Cruz Nundaco","Santa Cruz Tacache de Mina","Santa Cruz Tacahua","Santa Cruz Tayata","Santa Cruz Xitla","Santa Cruz Xoxocotlán","Santa Gertrudis","Santa Inés de Zaragoza","Santa Inés del Monte","Santa Inés Yatzeche","Santa Lucía del Camino","Santa María Alotepec","Santa María Apazco","Santa María Atzompa","Santa María Camotlán","Santa María Chachoápam","Santa María Chilchotla","Santa María Chimalapa","Santa María Colotepec","Santa María Cortijo","Santa María Coyotepec","Santa María Ecatepec","Santa María Guelacé","Santa María Guienagati","Santa María Huatulco","Santa María Huazolotitlán","Santa María Ipalapa","Santa María Ixcatlán","Santa María Jacatepec","Santa María Jalapa del Marqués","Santa María Jaltianguis","Santa María Lachixío","Santa María Mixtequilla","Santa María Nduayaco","Santa María Nazaret","Santa María Petapa","Santa María Quiegolani","Santa María Sola","Santa María Tataltepec","Santa María Tecomavaca","Santa María Temaxcalapa","Santa María Temaxcaltepec","Santa María Teopoxco","Santa María Tepantlali","Santa María Texcatitlán","Santa María Tlahuitoltepec","Santa María Tlalixtac","Santa María Tonameca","Santa María Xadani","Santa María Yalina","Santa María Yavesía","Santa María Yolotepec","Santa María Yosoyúa","Santa María Yucuhiti","Santa María Zacatepec","Santa María Zaniza","Santa María Zoquitlán","Santiago Amoltepec","Santiago Apoala","Santiago Apóstol","Santiago Astata","Santiago Atitlán","Santiago Ayuquililla","Santiago Cacaloxtepec","Santiago Camotlán","Santiago Choápam","Santiago del Río","Santiago Huajolotitlán","Santiago Huauclilla","Santiago Ihuitlán Plumas","Santiago Ixcuintepec","Santiago Ixtayutla","Santiago Jamiltepec","Santiago Jocotepec","Santiago Juxtlahuaca","Santiago Lachiguiri","Santiago Lalopa","Santiago Laollaga","Santiago Laxopa","Santiago Llano Grande","Santiago Matatlán","Santiago Miltepec","Santiago Minas","Santiago Nacaltepec","Santiago Nejapilla","Santiago Niltepec","Santiago Nundiche","Santiago Nuyoó","Santiago Pinotepa Nacional","Santiago Suchilquitongo","Santiago Tamazola","Santiago Tapextla","Santiago Tenango","Santiago Tepetlapa","Santiago Tetepec","Santiago Texcalcingo","Santiago Textitlán","Santiago Tilantongo","Santiago Tillo","Santiago Tlazoyaltepec","Santiago Xanica","Santiago Xiacuí","Santiago Yaitepec","Santiago Yaveo","Santiago Yolomécatl","Santiago Yosondúa","Santiago Yucuyachi","Santiago Zacatepec","Santiago Zoochila","Santo Domingo Ingenio","Santo Domingo Albarradas","Santo Domingo Armenta","Santo Domingo Chihuitán","Santo Domingo de Morelos","Santo Domingo Ixcatlán","Santo Domingo Nuxaá","Santo Domingo Tehuantepec","Santo Domingo Teojomulco","Santo Domingo Tepuxtepec","Santo Domingo Xanica","Santo Domingo Yanhuitlán","Santo Domingo Yodohino","Santo Domingo Zanatepec","Santo Tomás Jalieza","Santo Tomás Mazaltepec","Santo Tomás Ocotepec","Santa María Tonameca","Santos Reyes Nopala","Santos Reyes Pápalo","Santos Reyes Tepejillo","Santos Reyes Yucuná","Silacayoápam","Sitio de Xitlapehua","Sola de Vega","Solaná","Sola de Vega","Sola de Vega","Sola de Vega","Sola de Vega","Sola de Vega","Sola de Vega","Sola de Vega","Sola de Vega","Sola de Vega","Sola de Vega","Sola de Vega","Sola de Vega"],
  "Puebla": ["Acajete","Acateno","Acatlán","Acaxochitlán","Acteopan","Ahuacatlán","Ahuazotepec","Ahuehuetitla","Ajalpan","Albino Zertuche","Aljojuca","Altepexi","Amixtlán","Amozoc","Aquixtla","Atempan","Atexcal","Atlixco","Atoyatempan","Atzala","Atzitzihuacán","Atzitzintla","Axutla","Ayotoxco de Guerrero","Calpan","Caltepec","Camocuautla","Caxhuacan","Coatepec","Coatzingo","Cohetzala","Cohuecan","Coronango","Coyotepec","Cuautempan","Cuautinchán","Cuautlancingo","Cuayuca de Andrade","Cuetzalan del Progreso","Cuyoaco","Chalchicomula de Sesma","Chapulco","Chiautla","Chiautzingo","Chichiquila","Chiconcuautla","Chichón","Chietla","Chigmecatitlán","Chignahuapan","Chignautla","Chila","Chila de la Sal","Honey","Chilchotla","Chinantla","Cohetzala","Chignahuapan","Chignautla","Chila","Chila de la Sal","Honey","Chilchotla","Chinantla","Chila de la Sal","Chiconcuautla","Chicontepec","Chicontepec","Chicontepec","Chicontepec","Chicontepec","Chicontepec","Chicontepec","Chicontepec","Chicontepec","Chicontepec","Chicontepec","Chicontepec"],
  "Querétaro": ["Querétaro", "Amealco de Bonfil","Arroyo Seco","Cadereyta de Montes","Colón","Corregidora","Ezequiel Montes","Huimilpan","Jalpan de Serra","Landa de Matamoros","El Marqués","Pedro Escobedo","Peñamiller","San Joaquín","San Juan del Río","Tequisquiapan","Tolimán"],
  "Quintana Roo": ["Cozumel","Felipe Carrillo Puerto","Isla Mujeres","Othón P. Blanco","Benito Juárez","José María Morelos","Lázaro Cárdenas","Solidaridad","Tulum"],
  "San Luis Potosí": ["Ahualulco","Alaquines","Aquismón","Armadillo de los Infante","Cd. del Maíz","Cárdenas","Cedral","Cerritos","Cerro de San Pedro","Ciudad Fernández","Tancanhuitz","Tanlajás","Tanquián de Escobedo","Tampacán","Tampamolón Corona","Tamuín","Tanquián de Escobedo","Tamuín","Tancanhuitz","Tanlajás","Tanquián de Escobedo","Tampacán","Tampamolón Corona","Tamuín","Tanquián de Escobedo"],
  "Sinaloa": ["Ahome","Angostura","Badiraguato","Concordia","Cosalá","Culiacán","El Fuerte","Escuinapa","Guasave","Mazatlán","Mocorito","Rosario","Salvador Alvarado","San Ignacio","Sinaloa","Navolato"],
  "Sonora": ["Aconchi","Agua Prieta","Alamos","Altar","Arivechi","Arizpe","Álamos","Bacadéhuachi","Bacanora","Bacerac","Bacoachi","Bácum","Banámichi","Baviácora","Bavispe","Benjamín Hill","Caborca","Cajeme","Cananea","Carbó","La Colorada","Cucurpe","Cumpas","Divisaderos","Empalme","Etchojoa","Fronteras","General Plutarco Elías Calles","Granados","Guaymas","Hermosillo","Huachinera","Huásabas","Huatabampo","Huepac","Imuris","Magdalena","Mazatán","Moctezuma","Naco","Nácori Chico","Navojoa","Nogales","Onavas","Opodepe","Oquitoa","Pitiquito","Puerto Peñasco","Quiriego","Rayón","Rosario","Sahuaripa","San Felipe de Jesús","San Javier","San Luis Río Colorado","San Miguel de Horcasitas","San Pedro de la Cueva","Santa Ana","Santa Cruz","Sáric","Soyopa","Suaqui Grande","Tepache","Trincheras","Tubutama","Ures","Villa Hidalgo","Yécora"],
  "Tabasco": ["Balancán","Cárdenas","Centla","Centro","Comalcalco","Cunduacán","Emiliano Zapata","Huimanguillo","Jalapa","Jalpa de Méndez","Jonuta","Macuspana","Nacajuca","Paraíso","Tacotalpa","Teapa","Tenosique"],
  "Tamaulipas": ["Abasolo","Aldama","Altamira","Antiguo Morelos","Burgos","Bustamante","Camargo","Casas","Ciudad Madero","Cruillas","Gómez Farías","González","Guerrero","Gustavo Díaz Ordaz","Hidalgo","Jaumave","Jiménez","Llera","Mainero","El Mante","Matamoros","Méndez","Mier","Miguel Alemán","Miquihuana","Nuevo Laredo","Nuevo Morelos","Ocampo","Padilla","Palmillas","Reynosa","Río Bravo","San Carlos","San Fernando","San Nicolás","Soto la Marina","Tampico","Tula","Valle Hermoso","Victoria","Villagrán","Xicoténcatl"],
  "Tlaxcala": ["Acuitlapilco","Amaxac de Guerrero","Apetatitlán de Antonio Carvajal","Apizaco","Atlangatepec","Atltzayanca","Benito Juárez","Calpulalpan","Cuapiaxtla","El Carmen Tequexquitla","Españita","Huamantla","Hueyotlipan","Ixtacuixtla de Mariano Matamoros","Ixtenco","Mazatecochco de José María Morelos","Contla de Juan Cuamatzi","La Magdalena Tlaltelulco","Lázaro Cárdenas","Mazatlán Villa de Flores","Muñoz de Domingo Arenas","Nativitas","Panotla","Papalotla de Xicohténcatl","San Damián Texoloc","San Francisco Tetlanohcan","San Jerónimo Zacualpan","San José Teacalco","San Juan Huactzinco","San Lorenzo Axocomanitla","San Lucas Tecopilco","San Pablo del Monte","Sanctórum de Lázaro Cárdenas","Santa Ana Nopalucan","Santa Apolonia Teacalco","Santa Catarina Ayometla","Santa Cruz Quilehtla","Santa Isabel Xiloxoxtla","Tenancingo","Teolocholco","Tepetitla de Lardizábal","Tepeyanco","Terrenate","Tetla de la Solidaridad","Tetlatlahuca","Tlaxcala","Tlaxco","Tocatlan","Totolac","Xaloztoc","Xaltocan","Xicohtzinco","Yauhquemehcan","Zacatelco","Ziltlaltépec de Trinidad Sánchez Santos","Zoncuautla"],
  "Veracruz": ["Acajete","Acultzingo","Agua Dulce","Alpatláhuac","Alto Lucero de Gutiérrez Barrios","Altotonga","Alvarado","Amatitlán","Amatlán de los Reyes","Angel R. Cabada","Apazapan","Aquila","Astacinga","Atlahuilco","Atoyac","Atzacan","Atzalan","Ayahualulco","Banderilla","Benito Juárez","Boca del Río","Calcahualco","Camerino Z. Mendoza","Carlos A. Carrillo","Carrillo Puerto","Catemaco","Cazones de Herrera","Cerro Azul","Citlaltépetl","Coacoatzintla","Coahuitlán","Coatepec","Coatzacoalcos","Coatzintla","Cosamaloapan de Carpio","Cosautlán de Carvajal","Coscomatepec","Cosoleacaque","Cotaxtla","Coxquihui","Coyutla","Cuichapa","Cuitláhuac","Chacaltianguis","Chalma","Chiconamel","Chiconquiaco","Chicontepec","Chinameca","Chinampa de Gorostiza","Chocamán","Chontla","Chumatlán","Emiliano Zapata","Espinal","Filomeno Mata","Fortín","Gutiérrez Zamora","Hidalgotitlán","Huatusco","Huayacocotla","Hueyapan de Ocampo","Huiloapan de Cuauhtémoc","Ignacio de la Llave","Ilamatlán","Isla","Ixcatepec","Ixhuacán de los Reyes","Ixhuatlán de Madero","Ixhuatlán del Café","Ixhuatlán del Sureste","Ixhuatlancillo","Ixtaczoquitlán","Jalacingo","Jalcomulco","Jáltipan","Jamapa","Jesús Carranza","Jilotepec","Juan Rodríguez Clara","Juchique de Ferrer","Landero y Coss","Lerdo de Tejada","Magdalena","Maltrata","Manlio Fabio Altamirano","Mariano Escobedo","Martínez de la Torre","Mecatlán","Mecayapan","Medellín","Miahuatlán","Minatitlán","Mixtla de Altamirano","Moloacán","Nanchital de Lázaro Cárdenas del Río","Naolinco","Naranjal","Nautla","Nogales","Oluta","Omealca","Orizaba","Otatitlán","Oteapan","Ozuluama de Mascareñas","Pajapan","Pánuco","Papantla","Paso del Macho","Paso de Ovejas","La Perla","Perote","Platón Sánchez","Playa Vicente","Poza Rica de Hidalgo","Pueblo Viejo","Puente Nacional","Rafael Delgado","Rafael Lucio","Los Reyes","Río Blanco","Saltabarranca","San Andrés Tenejapan","San Andrés Tuxtla","San Juan Evangelista","San Rafael","Santiago Tuxtla","Sayula de Alemán","Soconusco","Sochiapa","Soledad Atzompa","Soledad de Doblado","Soteapan","Tamalín","Tamalín","Tamiahua","Tampico Alto","Tancoco","Tantima","Tantoyuca","Tatatila","Tatatila","Tecolutla","Tehuipango","Tempoal","Tenampa","Tenixtepec","Teocelo","Tequila","Tepatlaxco","Tepetzintla","Tequila","Tierra Blanca","Tihuatlán","Tlacojalpan","Tlacolulan","Tlacotalpan","Tlachichilco","Tlacotepec de Mejía","Tlapacoyan","Tlaquilpa","Tlaquilpa","Tlaquilpa","Tlaquilpa","Tlaquilpa","Tlilapan","Tomatlán","Tonayán","Totutla","Tuxpan","Tuxtilla","Uxpanapa","Vega de Alatorre","Veracruz","Villa Aldama","Xalapa","Xico","Yanga","Yecuatla","Zacualpan","Zaragoza","Zentla","Zongolica","Zontecomatlán de López y Fuentes","Zozocolco de Hidalgo","Agua Dulce","Acula","Amatitlán","Amatlán de los Reyes","Atlahuilco","Atoyac","Atzacan","Atzalan","Camino Real","Carlos A. Carrillo","Carrillo Puerto","Chacaltianguis","Chiconquiaco","Chinameca","Chocamán","Chontla","Cotaxtla","Coyutla","Cuichapa","Cuitláhuac","El Higo","Emiliano Zapata","Espinal","Filomeno Mata","Fortín","Gutiérrez Zamora","Hidalgotitlán","Huayacocotla","Huazuntlán","Hueyapan de Ocampo","Huiloapan de Cuauhtémoc","Ignacio de la Llave","Ilamatlán","Isla","Ixcatepec","Ixhuacán de los Reyes","Ixhuatlán de Madero","Ixhuatlán del Sureste","Ixhuatlancillo","Ixtaczoquitlán","Jalacingo","Jalcomulco","Jáltipan","Jamapa","Jesús Carranza","Jilotepec","Juan Rodríguez Clara","Juchique de Ferrer","Landero y Coss","Las Choapas","Las Minas","Las Vigas de Ramírez","León","Lerdo de Tejada","Magdalena","Maltrata","Manlio Fabio Altamirano","Mariano Escobedo","Martínez de la Torre","Mecatlán","Mecayapan","Medellín","Miahuatlán","Minatitlán","Misantla","Mixtla de Altamirano","Moloacán","Naolinco","Naranjal","Nautla","Nogales","Oluta","Omealca","Orizaba","Otatitlán","Oteapan","Ozuluama de Mascareñas","Pajapan","Pánuco","Papantla","Paso del Macho","Paso de Ovejas","La Perla","Perote","Platón Sánchez","Playa Vicente","Poza Rica de Hidalgo","Pueblo Viejo","Puente Nacional","Rafael Delgado","Rafael Lucio","Los Reyes","Río Blanco","Saltabarranca","San Andrés Tenejapan","San Andrés Tuxtla","San Juan Evangelista","San Rafael","Santiago Tuxtla","Sayula de Alemán","Soconusco","Sochiapa","Soledad Atzompa","Soledad de Doblado","Soteapan","Tamalín","Tamiahua","Tampico Alto","Tancoco","Tantima","Tantoyuca","Tatatila","Tecolutla","Tehuipango","Tempoal","Tenampa","Tenixtepec","Teocelo","Tequila","Tepatlaxco","Tepetzintla","Tequila","Tierra Blanca","Tihuatlán","Tlacojalpan","Tlacolulan","Tlacotalpan","Tlachichilco","Tlacotepec de Mejía","Tlapacoyan","Tlaquilpa","Tlilapan","Tomatlán","Tonayán","Totutla","Tuxpan","Tuxtilla","Uxpanapa","Vega de Alatorre","Veracruz","Villa Aldama","Xalapa","Xico","Yanga","Yecuatla","Zacualpan","Zaragoza","Zentla","Zongolica","Zontecomatlán de López y Fuentes","Zozocolco de Hidalgo"],
  "Yucatán": ["Abalá","Acanceh","Akil","Baca","Bokobá","Buctzotz","Cacalchén","Calotmul","Cansahcab","Cantamayec","Celestún","Cenotillo","Conkal","Cuncunul","Cuzamá","Chacsinkín","Chankom","Chapab","Chemax","Chicxulub Pueblo","Chichimilá","Chikindzonot","Chocholá","Chumayel","Dzan","Dzemul","Dzidzantún","Dzitás","Dzoncauich","Espita","Halachó","Hocabá","Hoctún","Homún","Huhí","Hunucmá","Ixil","Izamal","Kanasín","Kantunil","Kaua","Kinchil","Kopomá","Mama","Maní","Maxcanú","Mayapán","Mocochá","Motul","Muxupip","Opichén","Oxkutzcab","Panabá","Peto","Progreso","Quintana Roo","Río Lagartos","Sacalum","Samahil","Sanahcat","San Felipe","Santa Elena","Seyé","Sinanché","Sotuta","Sucilá","Sudzal","Suma","Tahdziú","Tahmek","Teabo","Tecoh","Tekal de Venegas","Tekantó","Tekax","Tekit","Tekom","Telchac Pueblo","Temax","Temozón","Tenabo","Ticul","Timucuy","Tinum","Tixcacalcupul","Tixkokob","Tixméhuac","Tixpéual","Tizimín","Tunkás","Tzucacab","Uayma","Ucú","Umán","Valladolid","Xocchel","Yaxcabá","Yaxkukul","Yobaín"],
  "Zacatecas": ["Apozol","Apulco","Atolinga","Benito Juárez","Calera","Cañitas de Felipe Pescador","Concepción del Oro","Cuauhtémoc","Chalchihuites","Fresnillo","Trinidad García de la Cadena","Genaro Codina","General Enrique Estrada","General Francisco R. Murguía","El Plateado de Joaquín Amaro","Jalpa","Jerez","Jiménez del Teul","Juan Aldama","Juchipila","Loreto","Luis Moya","Mazapil","Melchor Ocampo","Mezquital del Oro","Miguel Auza","Momax","Monte Escobedo","Morelos","Moyahua de Estrada","Nochistlán de Mejía","Noria de Ángeles","Ojocaliente","Pánuco","Pinos","Río Grande","Sain Alto","El Salvador","Sombrerete","Susticacán","Tabasco","Tepechitlán","Tepetongo","Teúl de González Ortega","Tlaltenango de Sánchez Román","Valparaíso","Vetagrande","Villa de Cos","Villa García","Villa González Ortega","Villa Hidalgo","Villanueva","Zacatecas"]
  }
};

  // Helper para reaplicar tipo de persona tras recarga
  function aplicarTipoPersonaGuardado() {
    const tipo = localStorage.getItem("tipoPersonaMCV");
    if (!tipo) return;
    document.body.dataset.tipoPersona = tipo;

    const bloquesMoral  = document.querySelectorAll('[data-solo-moral]');
    const bloquesFisica = document.querySelectorAll('[data-solo-fisica]');

    if (tipo === 'física') {
      bloquesMoral.forEach(b => b.style.display = 'none');
      bloquesFisica.forEach(b => b.style.display = 'flex');
    } else {
      bloquesMoral.forEach(b => b.style.display = 'flex');
      bloquesFisica.forEach(b => b.style.display = 'none');
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("form-contrato");
    const fieldsets = form.querySelectorAll("fieldset");
    const fsArray = Array.from(fieldsets);
    let currentStep = 0;
    const storageKey = "formularioContratoMCV";

    function detectarPasoDesdeDatos(data) {
      for (let i = fieldsets.length - 1; i >= 0; i--) {
        const inputs = fieldsets[i].querySelectorAll("input, select");
        for (let input of inputs) {
          if (data[input.name] && data[input.name].trim() !== "") {
            return i;
          }
        }
      }
      return 0;
    }

    function mostrarBotonBorrarSiAplica(data) {
      const hayDatos = Object.values(data).some(v => v && v.trim() !== "");
      if (hayDatos) {
        document.getElementById("borrar-borrador").style.display = "block";
      }
    }

    const dataGuardada = localStorage.getItem(storageKey);
    let mostrarModal = true;

    if (dataGuardada) {
      const data = JSON.parse(dataGuardada);
      const hayDatos = Object.keys(data).some(k => k !== "razon_social" && k !== "sucursal" && data[k].trim() !== "");
      if (hayDatos) {
        currentStep = detectarPasoDesdeDatos(data);
        form.style.display = "block";
        cargarDatos();
        aplicarTipoPersonaGuardado();
        showStep(currentStep);
        mostrarBotonBorrarSiAplica(data);
        mostrarModal = false;
      } else {
        localStorage.removeItem(storageKey);
      }
    }

    if (mostrarModal) {
    const modal = document.createElement("div");
    modal.id = "modal-inicio";
    modal.innerHTML = `
    <head>
      <link rel="stylesheet" href="style.css">
    </head>
      <div class="modal-contenido">
        <h2>Inicio</h2>
        <label>Razón social:<br>
          <input type="text" id="razon-social-modal" placeholder="Ej. Grupo Hidroregio Ambiental S.A. de C.V.">
        </label>

        <p>¿Tipo de persona?</p>
        <div class="tipo-persona-toggle">
          <button type="button" class="toggle-btn active" data-value="moral">Moral</button>
          <button type="button" class="toggle-btn" data-value="física">Física</button>
        </div>
        <input type="hidden" name="tipo-persona" id="tipo-persona" value="moral">

        <p>Selecciona tu sucursal:</p>
        <div>
          <button id="btn-geolocalizar" class="btn">📍 Usar ubicación</button>
          <button id="btn-manual" class="btn">📝 Elegir manualmente</button>
        </div>
        <div id="manual-select" style="margin-top:1em; display:none;">
          <select id="sucursal-lista">
            <option value="">Selecciona una sucursal</option>
            <option value="mty">Monterrey</option>
            <option value="ags">Aguascalientes</option>
            <option value="qro">Querétaro</option>
          </select>
        </div>
        <button id="acceder-modal" class="btn" style="margin-top:1em;">Acceder</button>
      </div>`;
    modal.classList.add("modal");
    document.body.appendChild(modal);

    // ─── Inicializa el toggle de Tipo de Persona ─────────────────────────────
    document.querySelectorAll(".toggle-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        // Desactiva todas y activa solo la clickeada
        document.querySelectorAll(".toggle-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        // Guarda el valor en el hidden
        document.getElementById("tipo-persona").value = btn.dataset.value;
      });
    });
  }

  document.getElementById("btn-manual")?.addEventListener("click", () => {
    document.getElementById("manual-select").style.display = "block";
  });

  document.getElementById("btn-geolocalizar")?.addEventListener("click", () => {
    if (!navigator.geolocation) return mostrarError("Tu navegador no permite geolocalización.");

    navigator.geolocation.getCurrentPosition(async pos => {
      const lat = pos.coords.latitude;
      const lon = pos.coords.longitude;
      try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`, {
          headers: { 'User-Agent': 'mcv-formulario/1.0 (mediosconvalor.com)' }
        });
        const json = await res.json();
        const estado = json.address.state || "";
        let sucursal = "qro";
        if (/aguascalientes/i.test(estado)) sucursal = "ags";
        else if (/nuevo le[oó]n/i.test(estado)) sucursal = "mty";
        document.getElementById("sucursal-lista").value = sucursal;
        document.getElementById("manual-select").style.display = "block";
      } catch (e) {
        mostrarError("No se pudo determinar tu ubicación automáticamente.");
        document.getElementById("manual-select").style.display = "block";
      }
    });
  });

  document.getElementById("acceder-modal")?.addEventListener("click", () => {
    const razon = document.getElementById("razon-social-modal").value.trim();
    const sucursal = document.getElementById("sucursal-lista").value;
    const tipoPersona = document.getElementById("tipo-persona").value;

    if (!razon || !sucursal || !tipoPersona) {
      return mostrarError("Completa todos los campos del inicio.");
    }

    // Guarda en localStorage
    localStorage.setItem(storageKey, JSON.stringify({ razon_social: razon, sucursal }));
    localStorage.setItem("tipoPersonaMCV", tipoPersona);

    // Oculta modal y muestra formulario
    document.body.dataset.tipoPersona = tipoPersona;
    document.getElementById("modal-inicio").remove();
    form.style.display = "block";
    cargarDatos();

    aplicarTipoPersonaGuardado();              // ← Inicializa botones según tipo
    showStep(currentStep);

    // Ajusta enlace de WhatsApp
    const waMap = { mty: "528123575710", ags: "524492656569", qro: "524424710760" };
    document.getElementById("whatsapp-float").href = `https://wa.me/${waMap[sucursal] || waMap.qro}`;
  });

  function showStep(index) {
    while (
      index < fieldsets.length &&
      fieldsets[index].hasAttribute("data-solo-moral") &&
      document.body.dataset.tipoPersona === "física"
    ) {
      index++; // Salta si es solo-moral
    }
    fieldsets.forEach((fs, i) => {
      const soloMoral = fs.hasAttribute("data-solo-moral");
      const ocultar = i !== index || (soloMoral && document.body.dataset.tipoPersona === "física");
      fs.style.display = ocultar ? "none" : "block";
    });
    currentStep = index;
  }

  function guardarPaso(index) {
    const data = JSON.parse(localStorage.getItem(storageKey)) || {};
    const inputs = fieldsets[index].querySelectorAll("input, select");
    inputs.forEach(input => {
      if (input.name) data[input.name] = input.value;
    });
    localStorage.setItem(storageKey, JSON.stringify(data));
  }

  function cargarDatos() {
    const data = JSON.parse(localStorage.getItem(storageKey));
    if (!data) return;
    document.querySelectorAll("input, select").forEach(el => {
      if (data[el.name]) el.value = data[el.name];
    });
  }

  function avanzar() {
    const inputs = fieldsets[currentStep].querySelectorAll("input[required], select[required]");
    for (let input of inputs) {
      if (!input.value.trim()) {
        mostrarError("Por favor completa todos los campos requeridos.");
        return;
      }
    }

    guardarPaso(currentStep);

    const tipo = document.body.dataset.tipoPersona;
    const pasoFinal = tipo === 'moral' ? 5 : 4;

    if (currentStep < pasoFinal) {
      currentStep++;
      showStep(currentStep);
    }
  }

  function retroceder() {
    if (currentStep > 0) {
      currentStep--;
      showStep(currentStep);
    }
  }

  form.addEventListener("click", e => {
    if (e.target.classList.contains("siguiente")) avanzar();
    if (e.target.classList.contains("anterior")) retroceder();
  });

  form.addEventListener("submit", e => {
    // ─── Determinar el último paso antes del envío según tipo de persona ───
    const tipo = document.body.dataset.tipoPersona;
    // Para “moral” el último paso es el de representante (dataset.step="5" → índice 4),
    // para “física” el último es Pagos (dataset.step="4" → índice 3).
    const pasoFinal = (tipo === "moral")
      ? fsArray.length - 1
      : fsArray.findIndex(fs => fs.dataset.step === "4");
    if (currentStep < pasoFinal) {
      e.preventDefault();
      avanzar();
      return;
  }

    e.preventDefault();
    guardarPaso(currentStep);
    const datos = JSON.parse(localStorage.getItem(storageKey));
    const btnEnviar = document.querySelector("#btn-enviar, #btn-enviar-fisica");
    btnEnviar.disabled = true;
    btnEnviar.textContent = "Enviando...";
    fetch("https://script.google.com/macros/s/AKfycbxutyZXZRJVIAXJNOXmF79BWRV_sr7YwG2kvYBCY4Pde7TKnSwnfMcouyPy0dK5QWfdvw/exec", {
      method: "POST",
      mode: "no-cors",
      body: JSON.stringify(datos)
    })
      .then(() => {
        localStorage.removeItem(storageKey);
        document.getElementById("modal-exito").style.display = "flex";
        document.getElementById("form-contrato").style.display = "none";

        const tipo = document.body.dataset.tipoPersona;
        const texto = document.getElementById("exito-text");
        const lista = document.getElementById("exito-docs");

        // Mensaje común
        texto.textContent = "Por favor comparte los siguientes documentos al correo calidadqro@mediosconvalor.com:";

        if (tipo === "física") {
          // Documentos para persona física
          const docsFisica = [
            "Identificación oficial",
            "Constancia de situación fiscal",
            "Comprobante de domicilio",
            "Carátula del estado de cuenta bancario"
          ];
          lista.innerHTML = docsFisica.map(d => `<li>${d}</li>`).join("");
        } else {
          // Documentos para persona moral
          const docsMoral = [
            "Acta constitutiva",
            "Registro Público de Comercio",
            "Identificación oficial del representante legal",
            "Poder notarial",
            "Constancia de situación fiscal",
            "Comprobante de domicilio",
            "Carátula del estado de cuenta bancario",
            "Licencia de funcionamiento"
          ];
          lista.innerHTML = docsMoral.map(d => `<li>${d}</li>`).join("");
        }
      })
      .catch(() => mostrarError("Hubo un problema de conexión con el servidor."))
      .finally(() => {
        btnEnviar.disabled = false;
        btnEnviar.textContent = "Enviar";
      });
  });

  document.getElementById("borrar-borrador").addEventListener("click", () => {
    confirmarAccion("¿Deseas borrar el borrador actual?", () => {
      localStorage.removeItem(storageKey);
      localStorage.removeItem("tipoPersonaMCV");
      location.reload();
    });
  });

  document.getElementById("exportar-json").addEventListener("click", () => {
    const data = localStorage.getItem(storageKey);
    if (data) {
      const blob = new Blob([data], { type: "application/json" });
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = "borrador_formulario.json";
      a.click();
      URL.revokeObjectURL(url);
    } else mostrarError("No hay datos para exportar.");
  });

  // Bancos
  const selBanco = document.getElementById("banco-select");
  selBanco.innerHTML = '<option value="" disabled selected>Selecciona una opción</option>';
  bancosMX.instituciones.forEach(nombre => {
    const opt = document.createElement("option");
    opt.value = nombre;
    opt.textContent = nombre;
    selBanco.appendChild(opt);
  });
  selBanco.addEventListener("change", e => {
    document.getElementById("banco-otro-label").style.display = e.target.value.toLowerCase().includes("otro") ? "block" : "none";
  });

  // Régimen fiscal
  const selRegimen = document.getElementById("regimen-select");
  selRegimen.innerHTML = '<option value="" disabled selected>Selecciona una opción</option>';
  regimenesMX.regimenes.forEach(nombre => {
    const opt = document.createElement("option");
    opt.value = nombre;
    opt.textContent = nombre;
    selRegimen.appendChild(opt);
  });

  // Estados / Municipios (2 secciones)
  function poblarEstadosYMunicipios(estadoSel, municipioSel) {
    estadoSel.innerHTML    = '<option value="" disabled selected>Selecciona un estado</option>';
    municipioSel.innerHTML = '<option value="" disabled selected>Selecciona un municipio</option>';
    estadosMunicipiosMX.estados.forEach(nombre => {
      const opt = document.createElement("option");
      opt.value = nombre;
      opt.textContent = nombre;
      estadoSel.appendChild(opt);
    });
    estadoSel.addEventListener("change", () => {
      municipioSel.innerHTML = "";
      (estadosMunicipiosMX.municipios[estadoSel.value] || []).forEach(m => {
        const opt = document.createElement("option");
        opt.value = m;
        opt.textContent = m;
        municipioSel.appendChild(opt);
      });
    });
  }

  poblarEstadosYMunicipios(
    document.getElementById("estado-select"),
    document.getElementById("municipio-select")
  );
  poblarEstadosYMunicipios(
    document.getElementById("estado-secundario-select"),
    document.getElementById("municipio-secundario-select")
  );
});

function mostrarError(texto) {
  const modal = document.getElementById("modal-error");
  document.getElementById("mensaje-error").textContent = texto || "Ocurrió un error inesperado.";
  modal.style.display = "flex";
  modal.style.zIndex = "99999";
}

function confirmarAccion(mensaje, callback) {
  const modal = document.getElementById("modal-confirmar");
  document.getElementById("mensaje-confirmar").textContent = mensaje;
  modal.style.display = "flex";
  const btn = document.getElementById("btn-confirmar-si");
  const clone = btn.cloneNode(true);
  btn.parentNode.replaceChild(clone, btn);
  clone.addEventListener("click", () => {
    modal.style.display = "none";
    callback();
  });
}