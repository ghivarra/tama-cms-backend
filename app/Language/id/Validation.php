<?php

// override core en language system validation or define your own en language validation message
return [
    // Core Messages
   'noRuleSets'            => 'Tidak ada aturan validasi yang digunakan.',
   'ruleNotFound'          => '<strong>{0}</strong> bukan salah satu dari sistem validasi.',
   'groupNotFound'         => '<strong>{0}</strong> bukan salah satu dari grup validasi.',
   'groupNotArray'         => '<strong>{0}</strong> grup validasi harus berupa array.',
   'invalidTemplate'       => '<strong>{0}</strong> bukan merupakan validasi template.',

    // Rule Messages
   'alpha'                 => '<b>{field}</b> hanya boleh diisi dengan huruf alfabet.',
   'alpha_dash'            => '<b>{field}</b> hanya boleh diisi dengan huruf alfabet, angka, underscore (_) dan strip (-).',
   'alpha_numeric'         => '<b>{field}</b> hanya boleh diisi dengan huruf alfabet dan angka.',
   'alpha_numeric_punct'   => '<b>{field}</b> hanya boleh diisi dengan huruf alfabet, spasi, dan karakter ini:  ~ ! # $ % & * - _ + = | : .',
   'alpha_numeric_space'   => '<b>{field}</b> hanya boleh diisi dengan huruf alfabet, angka, dan spasi.',
   'alpha_space'           => '<b>{field}</b> hanya boleh diisi dengan huruf alfabet dan spasi.',
   'decimal'               => '<b>{field}</b> hanya boleh diisi dengan angka desimal.',
   'differs'               => '<b>{field}</b> harus berbeda dengan <b>{param}</b>.',
   'equals'                => '<b>{field}</b> harus persis sama dengan <b>{param}</b>.',
   'exact_length'          => '<b>{field}</b> harus memiliki jumlah karakter yang sama dengan <b>{param}</b>.',
   'greater_than'          => '<b>{field}</b> harus lebih besar dibanding <b>{param}</b>.',
   'greater_than_equal_to' => '<b>{field}</b> harus lebih besar atau setara dengan <b>{param}</b>.',
   'hex'                   => '<b>{field}</b> hanya boleh diisi dengan angka hexadesimal.',
   'in_list'               => '<b>{field}</b> harus salah satu dari: [<b>{param}</b>].',
   'integer'               => '<b>{field}</b> harus berupa integer atau bilangan bulat.',
   'is_natural'            => '<b>{field}</b> harus berupa angka real tanpa negatif.',
   'is_natural_no_zero'    => '<b>{field}</b> harus berupa angka real tanpa negatif dan nol.',
   'is_not_unique'         => '<b>{field}</b> tidak tersedia di dalam database.',
   'is_unique'             => '<b>{field}</b> sudah digunakan.',
   'less_than'             => '<b>{field}</b> harus lebih kecil dibanding <b>{param}</b>.',
   'less_than_equal_to'    => '<b>{field}</b> harus lebih kecil atau setara dengan <b>{param}</b>.',
   'matches'               => '<b>{field}</b> harus sama persis dengan <b>{param}</b>.',
   'max_length'            => '<b>{field}</b> tidak boleh melebihi <b>{param}</b> karakter.',
   'min_length'            => '<b>{field}</b> tidak boleh kurang dari <b>{param}</b> karakter.',
   'not_equals'            => '<b>{field}</b> tidak boleh sama persis dengan <b>{param}</b>.',
   'numeric'               => '<b>{field}</b> hanya boleh diisi dengan angka.',
   'regex_match'           => '<b>{field}</b> menggunakan karakter yang tidak valid.',
   'required'              => '<b>{field}</b> belum diisi.',
   'required_with'         => '<b>{field}</b> harus diisi apabila <b>{param}</b> diisi.',
   'required_without'      => '<b>{field}</b> harus diisi apabila <b>{param}</b> tidak diisi.',
   'string'                => '<b>{field}</b> hanya boleh diisi dengan string.',
   'timezone'              => '<b>{field}</b> harus merupakan timezone yang valid.',
   'valid_base64'          => '<b>{field}</b> harus diisi dengan encoding base64.',
   'valid_email'           => '<b>{field}</b> harus diisi dengan akun email yang valid.',
   'valid_emails'          => '<b>{field}</b> harus diisi dengan semua akun email yang valid.',
   'valid_ip'              => '<b>{field}</b> tidak valid.',
   'valid_url'             => '<b>{field}</b> harus diisi dengan URL yang benar',
   'valid_date'            => '<b>{field}</b> harus diisi dengan tanggal yang tepat',

    // Credit Cards
   'valid_cc_num'          => '<b>{field}</b> tidak valid',

    // Files
   'uploaded'              => '<b>{field}</b> tidak valid.',
   'max_size'              => '<b>{field}</b> terlalu besar.',
   'is_image'              => '<b>{field}</b> bukan sebuah gambar.',
   'mime_in'               => '<b>{field}</b> bukan jenis gambar yang tepat.',
   'ext_in'                => '<b>{field}</b> bukan jenis gambar yang tepat.',
   'max_dims'              => '<b>{field}</b> antara bukan sebuah gambar, terlalu besar, atau terlalu lebar.',
];