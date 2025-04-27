-

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



--
-- Database: `voetbal_vereniging`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `email`
--

CREATE TABLE `email` (
  `emailadres` varchar(100) NOT NULL,
  `lidnummer` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `email`
--

INSERT INTO `email` (`emailadres`, `lidnummer`) VALUES
('janjansen@mail.com', 1),
('evadevries@mail.com', 2),
('mbakker@mail.com', 3),
('sofiavisser@mail.com', 4),
('larsp@mail.com', 5),
('annavdijk@mail.com', 6),
('bramklaassen@mail.com', 7),
('juliasmit@mail.com', 8),
('thomasmeijer@mail.com', 9),
('emmadj@mail.com', 10),
('hhhhh@gmail.nl', 11),
('tessvos@mail.nl', 12);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `lid`
--

CREATE TABLE `lid` (
  `lidnummer` int(11) NOT NULL,
  `naam` varchar(50) NOT NULL,
  `voornaam` varchar(50) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `huisnummer` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `lid`
--

INSERT INTO `lid` (`lidnummer`, `naam`, `voornaam`, `postcode`, `huisnummer`) VALUES
(1, 'Jansen', 'Jan', '1234AB', '12'),
(2, 'de Vries', 'Eva', '1234AB', '5B'),
(3, 'El Sayeed', 'Mohamed', '2011EF', '42'),
(4, 'Visser', 'Sofia', '9012EF', '7'),
(5, 'Petersen', 'Lars', '1234AB', '9'),
(6, 'van Dijk', 'Anna', '5678CD', '42'),
(7, 'Klaassen', 'Bram', '9012EF', '15A'),
(8, 'Smit', 'Julia', '1234AB', '3'),
(9, 'Meijer', 'Thomas', '5678CD', '66'),
(10, 'de Jong', 'Emma', '9012EF', '22'),
(11, 'Bos', 'Luuk', '1234AB', '200'),
(12, 'Vos', 'Tess', '1234AB', '15');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `postcode`
--

CREATE TABLE `postcode` (
  `postcode` varchar(10) NOT NULL,
  `adres` varchar(100) NOT NULL,
  `woonplaats` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `postcode`
--

INSERT INTO `postcode` (`postcode`, `adres`, `woonplaats`) VALUES
('1234AB', 'Hoofdstraat', 'Domburg'),
('1234BC', '', ''),
('2011EF', '', ''),
('5678CD', 'Marktplein', 'VrouwenPolder'),
('9012EF', 'Dorpsweg', 'Serooskerk');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `teamlid`
--

CREATE TABLE `teamlid` (
  `tl_ID` int(11) NOT NULL,
  `teamnaam` varchar(50) NOT NULL,
  `lidnummer` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `teamlid`
--

INSERT INTO `teamlid` (`tl_ID`, `teamnaam`, `lidnummer`) VALUES
(1, 'Senioren', 1),
(2, 'Senioren', 2),
(3, 'Senioren', 3),
(4, 'Senioren', 4),
(5, 'Junioren', 5),
(6, 'Junioren', 6),
(7, 'Junioren', 7),
(8, 'Junioren', 8),
(9, 'Pupillen', 9),
(10, 'Pupillen', 10),
(11, 'Pupillen', 11),
(12, 'Pupillen', 12);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `teams`
--

CREATE TABLE `teams` (
  `teamnaam` varchar(50) NOT NULL,
  `omschrijving` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `teams`
--

INSERT INTO `teams` (`teamnaam`, `omschrijving`) VALUES
('Junioren', 'Juniorenteam, onderverdeeld in onder-23, onder-19 en onder-15'),
('Pupillen', 'Pupillenteam, onderverdeeld in onder-13 en onder-10'),
('Senioren', 'Seniorenteam voor zowel heren als dames');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `telefoonnummers`
--

CREATE TABLE `telefoonnummers` (
  `telefoonnummer` varchar(15) NOT NULL,
  `lidnummer` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `telefoonnummers`
--

INSERT INTO `telefoonnummers` (`telefoonnummer`, `lidnummer`) VALUES
('0612345678', 1),
('0698765432', 2),
('0611223344', 3),
('0644556677', 4),
('0688776655', 5),
('0633445566', 7),
('0677889900', 8),
('0611223333', 9),
('0644555666', 10),
('0666777888', 11),
('0699988777', 12);

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `email`
--
ALTER TABLE `email`
  ADD PRIMARY KEY (`emailadres`),
  ADD KEY `lidnummer` (`lidnummer`);

--
-- Indexen voor tabel `lid`
--
ALTER TABLE `lid`
  ADD PRIMARY KEY (`lidnummer`),
  ADD KEY `postcode` (`postcode`);

--
-- Indexen voor tabel `postcode`
--
ALTER TABLE `postcode`
  ADD PRIMARY KEY (`postcode`);

--
-- Indexen voor tabel `teamlid`
--
ALTER TABLE `teamlid`
  ADD PRIMARY KEY (`tl_ID`),
  ADD KEY `teamnaam` (`teamnaam`),
  ADD KEY `lidnummer` (`lidnummer`);

--
-- Indexen voor tabel `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`teamnaam`);

--
-- Indexen voor tabel `telefoonnummers`
--
ALTER TABLE `telefoonnummers`
  ADD PRIMARY KEY (`telefoonnummer`),
  ADD KEY `lidnummer` (`lidnummer`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `lid`
--
ALTER TABLE `lid`
  MODIFY `lidnummer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT voor een tabel `teamlid`
--
ALTER TABLE `teamlid`
  MODIFY `tl_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `email`
--
ALTER TABLE `email`
  ADD CONSTRAINT `email_ibfk_1` FOREIGN KEY (`lidnummer`) REFERENCES `lid` (`lidnummer`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `lid`
--
ALTER TABLE `lid`
  ADD CONSTRAINT `lid_ibfk_1` FOREIGN KEY (`postcode`) REFERENCES `postcode` (`postcode`);

--
-- Beperkingen voor tabel `teamlid`
--
ALTER TABLE `teamlid`
  ADD CONSTRAINT `teamlid_ibfk_1` FOREIGN KEY (`teamnaam`) REFERENCES `teams` (`teamnaam`) ON DELETE CASCADE,
  ADD CONSTRAINT `teamlid_ibfk_2` FOREIGN KEY (`lidnummer`) REFERENCES `lid` (`lidnummer`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `telefoonnummers`
--
ALTER TABLE `telefoonnummers`
  ADD CONSTRAINT `telefoonnummers_ibfk_1` FOREIGN KEY (`lidnummer`) REFERENCES `lid` (`lidnummer`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
