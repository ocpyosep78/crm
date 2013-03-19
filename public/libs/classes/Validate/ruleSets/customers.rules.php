<?php

	return ['number'      => ['text', NULL, 10],
	        'customer'    => ['open', 2, 80],
	        'legal_name'  => ['open', 2, 80],
	        'rut'         => ['rut', NULL, 12],
	        'phone'       => ['phone', 3, 40],
	        'email'       => ['email', NULL, 50],
	        'address'     => ['open', NULL, 50],
	        'billingaddr' => ['open', NULL, 50],
	        'id_location' => ['selection']];