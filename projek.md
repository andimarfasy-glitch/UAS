{
  "project": {
    "name": "E-Commerce PHP Native",
    "version": "1.0.0",
    "technology": {
      "frontend": [
        "HTML5",
        "CSS3",
        "JavaScript",
        "Bootstrap 5"
      ],
      "backend": [
        "PHP Native"
      ],
      "database": [
        "MySQL"
      ],
      "server": [
        "Apache",
        "XAMPP"
      ]
    }
  },
  "ui_design": {
    "source": "desain.md",
    "theme": "Mengikuti desain.md",
    "responsive": true
  },
  "roles": [
    {
      "name": "Admin",
      "permissions": [
        "Kelola Produk",
        "Kelola Kategori",
        "Kelola User",
        "Kelola Pesanan",
        "Kelola Pembayaran",
        "Lihat Laporan"
      ]
    },
    {
      "name": "User",
      "permissions": [
        "Registrasi",
        "Login",
        "Melihat Produk",
        "Menambahkan ke Keranjang",
        "Checkout",
        "Pembayaran",
        "Melihat Riwayat Pesanan"
      ]
    }
  ],
  "pages": {
    "authentication": [
      {
        "name": "Login",
        "url": "/login.php",
        "features": [
          "Email",
          "Password",
          "Remember Me",
          "Forgot Password"
        ]
      },
      {
        "name": "Register",
        "url": "/register.php",
        "features": [
          "Nama Lengkap",
          "Email",
          "Password",
          "Konfirmasi Password"
        ]
      }
    ],
    "user": [
      {
        "name": "Home",
        "url": "/index.php"
      },
      {
        "name": "Daftar Produk",
        "url": "/produk.php"
      },
      {
        "name": "Detail Produk",
        "url": "/detail-produk.php"
      },
      {
        "name": "Keranjang",
        "url": "/cart.php"
      },
      {
        "name": "Checkout",
        "url": "/checkout.php"
      },
      {
        "name": "Pembayaran",
        "url": "/payment.php"
      },
      {
        "name": "Riwayat Pesanan",
        "url": "/orders.php"
      },
      {
        "name": "Profil",
        "url": "/profile.php"
      }
    ],
    "admin": [
      {
        "name": "Dashboard",
        "url": "/admin/dashboard.php"
      },
      {
        "name": "Manajemen Produk",
        "url": "/admin/produk.php"
      },
      {
        "name": "Manajemen Kategori",
        "url": "/admin/kategori.php"
      },
      {
        "name": "Manajemen User",
        "url": "/admin/user.php"
      },
      {
        "name": "Manajemen Pesanan",
        "url": "/admin/pesanan.php"
      },
      {
        "name": "Pembayaran",
        "url": "/admin/pembayaran.php"
      },
      {
        "name": "Laporan",
        "url": "/admin/laporan.php"
      }
    ]
  },
  "database": {
    "tables": [
      {
        "name": "users",
        "fields": [
          "id",
          "nama",
          "email",
          "password",
          "role",
          "created_at"
        ]
      },
      {
        "name": "categories",
        "fields": [
          "id",
          "nama_kategori",
          "created_at"
        ]
      },
      {
        "name": "products",
        "fields": [
          "id",
          "category_id",
          "nama_produk",
          "deskripsi",
          "harga",
          "stok",
          "gambar",
          "created_at"
        ]
      },
      {
        "name": "carts",
        "fields": [
          "id",
          "user_id",
          "product_id",
          "qty"
        ]
      },
      {
        "name": "orders",
        "fields": [
          "id",
          "user_id",
          "total_harga",
          "status",
          "alamat",
          "created_at"
        ]
      },
      {
        "name": "order_details",
        "fields": [
          "id",
          "order_id",
          "product_id",
          "harga",
          "qty",
          "subtotal"
        ]
      },
      {
        "name": "payments",
        "fields": [
          "id",
          "order_id",
          "payment_method",
          "transaction_id",
          "amount",
          "status",
          "paid_at"
        ]
      }
    ]
  },
  "payment_gateway": {
    "provider": "Midtrans",
    "features": [
      "QRIS",
      "Bank Transfer",
      "E-Wallet",
      "Credit Card"
    ],
    "integration": {
      "server_key": true,
      "client_key": true,
      "snap_payment": true,
      "callback_notification": true
    }
  },
  "folder_structure": {
    "root": {
      "assets": {
        "css": [],
        "js": [],
        "images": []
      },
      "config": [
        "database.php",
        "midtrans.php"
      ],
      "includes": [
        "header.php",
        "footer.php",
        "navbar.php"
      ],
      "auth": [
        "login.php",
        "register.php",
        "logout.php"
      ],
      "admin": [
        "dashboard.php",
        "produk.php",
        "kategori.php",
        "user.php",
        "pesanan.php",
        "laporan.php"
      ],
      "user": [
        "produk.php",
        "cart.php",
        "checkout.php",
        "orders.php",
        "profile.php"
      ]
    }
  },
  "workflow": [
    {
      "step": 1,
      "name": "User Registrasi"
    },
    {
      "step": 2,
      "name": "User Login"
    },
    {
      "step": 3,
      "name": "Melihat Produk"
    },
    {
      "step": 4,
      "name": "Tambah ke Keranjang"
    },
    {
      "step": 5,
      "name": "Checkout"
    },
    {
      "step": 6,
      "name": "Pilih Metode Pembayaran"
    },
    {
      "step": 7,
      "name": "Payment Gateway Midtrans"
    },
    {
      "step": 8,
      "name": "Notifikasi Pembayaran"
    },
    {
      "step": 9,
      "name": "Pesanan Diproses"
    },
    {
      "step": 10,
      "name": "Pesanan Selesai"
    }
  ],
  "security": {
    "authentication": [
      "Password Hashing",
      "Session Management"
    ],
    "validation": [
      "Server Side Validation",
      "Prepared Statement"
    ],
    "protection": [
      "CSRF Protection",
      "XSS Protection",
      "SQL Injection Protection"
    ]
  },
  "development_phases": [
    {
      "phase": 1,
      "name": "Analisis Kebutuhan",
      "duration": "3 Hari"
    },
    {
      "phase": 2,
      "name": "Desain UI berdasarkan desain.md",
      "duration": "5 Hari"
    },
    {
      "phase": 3,
      "name": "Pembuatan Database",
      "duration": "2 Hari"
    },
    {
      "phase": 4,
      "name": "Modul Login dan Register",
      "duration": "3 Hari"
    },
    {
      "phase": 5,
      "name": "Modul Produk dan Kategori",
      "duration": "5 Hari"
    },
    {
      "phase": 6,
      "name": "Modul Keranjang dan Checkout",
      "duration": "4 Hari"
    },
    {
      "phase": 7,
      "name": "Integrasi Midtrans",
      "duration": "3 Hari"
    },
    {
      "phase": 8,
      "name": "Dashboard Admin",
      "duration": "5 Hari"
    },
    {
      "phase": 9,
      "name": "Testing dan Debugging",
      "duration": "5 Hari"
    },
    {
      "phase": 10,
      "name": "Deployment",
      "duration": "2 Hari"
    }
  ]
}