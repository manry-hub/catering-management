create table bahan_baku
(
    id_bahan_baku       int auto_increment
        primary key,
    nama_bahan          varchar(100)                          not null,
    satuan              varchar(30)                           not null,
    harga_satuan_bahan  decimal(12, 2)                        not null,
    stok                decimal(10, 2) default 0.00           null,
    stok_kritis         decimal(10, 2) default 0.00           null,
    status_ketersediaan enum ('Tersedia', 'Menipis', 'Habis') null,
    constraint chk_bahan_baku_stok check (stok >= 0),
    constraint chk_bahan_baku_kritis check (stok_kritis >= 0)
);

create table cabang_kantin
(
    id_cabang_kantin int auto_increment
        primary key,
    nama_cabang      varchar(100) not null,
    alamat_cabang    text         null
);

create table catering
(
    id_catering   int auto_increment
        primary key,
    tanggal_acara date not null,
    lokasi_acara  text not null
);

create table pegawai
(
    id_pegawai   int auto_increment
        primary key,
    nama_pegawai varchar(100) not null,
    jabatan      varchar(50)  null,
    password     varchar(255) not null,
    constraint uq_pegawai_nama unique (nama_pegawai)
);

create table pelanggan
(
    id_pelanggan     int auto_increment
        primary key,
    nama_pelanggan   varchar(100) not null,
    no_telp          varchar(20)  null,
    alamat_pelanggan text         null
);

create table pesanan
(
    id_pesanan       int auto_increment
        primary key,
    tanggal_pesanan  datetime                    not null,
    status_pesanan   varchar(50)                 not null,
    total_harga      decimal(12, 2) default 0.00 null,
    jenis_pesanan    enum ('Kantin', 'Catering') not null,
    id_pelanggan     int                         not null,
    id_pegawai       int                         not null,
    id_cabang_kantin int                         null,
    id_catering      int                         null,
    catatan          text                        null,
    constraint fk_pesanan_cabang
        foreign key (id_cabang_kantin) references cabang_kantin (id_cabang_kantin)
            on update cascade on delete set null,
    constraint fk_pesanan_catering
        foreign key (id_catering) references catering (id_catering)
            on update cascade on delete set null,
    constraint fk_pesanan_pegawai
        foreign key (id_pegawai) references pegawai (id_pegawai)
            on update cascade,
    constraint fk_pesanan_pelanggan
        foreign key (id_pelanggan) references pelanggan (id_pelanggan)
            on update cascade
);

create table produk
(
    id_produk   int auto_increment
        primary key,
    nama_produk varchar(100)   not null,
    kategori    varchar(50)    null,
    harga_jual  decimal(12, 2) not null,
    harga_pokok decimal(12, 2) not null
);

create table bom
(
    id_produk     int            not null,
    id_bahan_baku int            not null,
    qty           decimal(10, 4) not null,
    subtotal      decimal(12, 2) not null,
    primary key (id_produk, id_bahan_baku),
    constraint fk_bom_bahan
        foreign key (id_bahan_baku) references bahan_baku (id_bahan_baku)
            on update cascade on delete cascade,
    constraint fk_bom_produk
        foreign key (id_produk) references produk (id_produk)
            on update cascade on delete cascade
);

create table detail_pesanan
(
    id_pesanan int            not null,
    id_produk  int            not null,
    qty        int            not null,
    subtotal   decimal(12, 2) not null,
    primary key (id_pesanan, id_produk),
    constraint fk_detail_pesanan
        foreign key (id_pesanan) references pesanan (id_pesanan)
            on update cascade on delete cascade,
    constraint fk_detail_produk
        foreign key (id_produk) references produk (id_produk)
            on update cascade
);
