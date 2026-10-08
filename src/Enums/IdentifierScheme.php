<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum IdentifierScheme: string
{
    case DUNS = 'DUNS';
    case EU_NAL = 'EU:NAL';
    case EU_REID = 'EU:REID';
    case EU_VAT = 'EU:VAT';
    case GLN = 'GLN';
    case GS1 = 'GS1';
    case IBAN = 'IBAN';
    case LEI = 'LEI';
    case SPIS = 'SPIS';
    case UBLBE = 'UBLBE';
    case AD_VAT = 'AD:VAT';
    case AE_TIN = 'AE:TIN';
    case AL_VAT = 'AL:VAT';
    case AT_CID = 'AT:CID';
    case AT_GOV = 'AT:GOV';
    case AT_KUR = 'AT:KUR';
    case AT_VAT = 'AT:VAT';
    case AU_ABN = 'AU:ABN';
    case BA_VAT = 'BA:VAT';
    case BE_EN = 'BE:EN';
    case BE_VAT = 'BE:VAT';
    case BG_VAT = 'BG:VAT';
    case CH_UIDB = 'CH:UIDB';
    case CH_VAT = 'CH:VAT';
    case CY_VAT = 'CY:VAT';
    case CZ_VAT = 'CZ:VAT';
    case DE_GEBA = 'DE:GEBA';
    case DE_LWID = 'DE:LWID';
    case DE_VAT = 'DE:VAT';
    case DK_CVR = 'DK:CVR';
    case DK_P = 'DK:P';
    case DK_SE = 'DK:SE';
    case EE_CC = 'EE:CC';
    case EE_VAT = 'EE:VAT';
    case ES_VAT = 'ES:VAT';
    case FI_OVT2 = 'FI:OVT2';
    case FR_CTC = 'FR:CTC';
    case FR_SIRENE = 'FR:SIRENE';
    case FR_SIRET = 'FR:SIRET';
    case FR_VAT = 'FR:VAT';
    case GB_VAT = 'GB:VAT';
    case GR_VAT = 'GR:VAT';
    case HR_VAT = 'HR:VAT';
    case HU_VAT = 'HU:VAT';
    case IE_VAT = 'IE:VAT';
    case IS_KT = 'IS:KT';
    case IS_KTNR = 'IS:KTNR';
    case IT_CFI = 'IT:CFI';
    case IT_COD = 'IT:COD';
    case IT_CUUO = 'IT:CUUO';
    case IT_FTI = 'IT:FTI';
    case IT_SECETI = 'IT:SECETI';
    case IT_SIA = 'IT:SIA';
    case IT_IVA = 'IT:IVA';
    case JP_IIN = 'JP:IIN';
    case JP_SST = 'JP:SST';
    case LI_VAT = 'LI:VAT';
    case LT_LEC = 'LT:LEC';
    case LT_VAT = 'LT:VAT';
    case LU_MAT = 'LU:MAT';
    case LU_VAT = 'LU:VAT';
    case LV_URN = 'LV:URN';
    case LV_VAT = 'LV:VAT';
    case MC_VAT = 'MC:VAT';
    case ME_VAT = 'ME:VAT';
    case MK_VAT = 'MK:VAT';
    case MT_VAT = 'MT:VAT';
    case MY_EIF = 'MY:EIF';
    case NG_TID = 'NG:TID';
    case NL_KVK = 'NL:KVK';
    case NL_OINO = 'NL:OINO';
    case NL_VAT = 'NL:VAT';
    case NO_ORG = 'NO:ORG';
    case NO_VAT = 'NO:VAT';
    case OM_VAT = 'OM:VAT';
    case PL_VAT = 'PL:VAT';
    case PT_VAT = 'PT:VAT';
    case RO_VAT = 'RO:VAT';
    case RS_VAT = 'RS:VAT';
    case SE_ORGNR = 'SE:ORGNR';
    case SG_UEN = 'SG:UEN';
    case SI_VAT = 'SI:VAT';
    case SK_DIC = 'SK:DIC';
    case SK_VAT = 'SK:VAT';
    case SM_VAT = 'SM:VAT';
    case TR_VAT = 'TR:VAT';
    case US_EIN = 'US:EIN';
    case VA_VAT = 'VA:VAT';

    /**
     * Get the scheme from its ISO 6523 ICD code, eg. 0184 for DK:CVR.
     *
     * @throws \ValueError When no scheme has the ICD code
     */
    public static function fromIcd(string $icd): self
    {
        return self::tryFromIcd($icd)
            ?? throw new \ValueError(sprintf('"%s" is not a valid ICD code for enum %s', $icd, self::class));
    }

    /**
     * Get the scheme from its ISO 6523 ICD code, or null when no scheme has the ICD code.
     */
    public static function tryFromIcd(string $icd): ?self
    {
        foreach (self::cases() as $scheme) {
            if ($scheme->icd() === $icd) {
                return $scheme;
            }
        }

        return null;
    }

    /**
     * The ISO 6523 ICD code, which Peppol uses as the identifier schemeID, eg. 0184 for DK:CVR.
     *
     * @see https://docs.peppol.eu/poacc/billing/3.0/codelist/eas/
     */
    public function icd(): string
    {
        return match ($this) {
            self::DUNS => '0060',
            self::EU_NAL => '0130',
            self::EU_REID => '9913',
            self::EU_VAT => '9912',
            self::GLN => '0088',
            self::GS1 => '0209',
            self::IBAN => '9918',
            self::LEI => '0199',
            self::SPIS => '0242',
            self::UBLBE => '0193',
            self::AD_VAT => '9922',
            self::AE_TIN => '0235',
            self::AL_VAT => '9923',
            self::AT_CID => '9916',
            self::AT_GOV => '9915',
            self::AT_KUR => '9919',
            self::AT_VAT => '9914',
            self::AU_ABN => '0151',
            self::BA_VAT => '9924',
            self::BE_EN => '0208',
            self::BE_VAT => '9925',
            self::BG_VAT => '9926',
            self::CH_UIDB => '0183',
            self::CH_VAT => '9927',
            self::CY_VAT => '9928',
            self::CZ_VAT => '9929',
            self::DE_GEBA => '0246',
            self::DE_LWID => '0204',
            self::DE_VAT => '9930',
            self::DK_CVR => '0184',
            self::DK_P => '0096',
            self::DK_SE => '0198',
            self::EE_CC => '0191',
            self::EE_VAT => '9931',
            self::ES_VAT => '9920',
            self::FI_OVT2 => '0216',
            self::FR_CTC => '0225',
            self::FR_SIRENE => '0002',
            self::FR_SIRET => '0009',
            self::FR_VAT => '9957',
            self::GB_VAT => '9932',
            self::GR_VAT => '9933',
            self::HR_VAT => '9934',
            self::HU_VAT => '9910',
            self::IE_VAT => '9935',
            self::IS_KT => '9917',
            self::IS_KTNR => '0196',
            self::IT_CFI => '0210',
            self::IT_COD => '0205',
            self::IT_CUUO => '0201',
            self::IT_FTI => '0097',
            self::IT_SECETI => '0142',
            self::IT_SIA => '0135',
            self::IT_IVA => '0211',
            self::JP_IIN => '0221',
            self::JP_SST => '0188',
            self::LI_VAT => '9936',
            self::LT_LEC => '0200',
            self::LT_VAT => '9937',
            self::LU_MAT => '0240',
            self::LU_VAT => '9938',
            self::LV_URN => '0218',
            self::LV_VAT => '9939',
            self::MC_VAT => '9940',
            self::ME_VAT => '9941',
            self::MK_VAT => '9942',
            self::MT_VAT => '9943',
            self::MY_EIF => '0230',
            self::NG_TID => '0244',
            self::NL_KVK => '0106',
            self::NL_OINO => '0190',
            self::NL_VAT => '9944',
            self::NO_ORG => '0192',
            self::NO_VAT => '9909',
            self::OM_VAT => '0248',
            self::PL_VAT => '9945',
            self::PT_VAT => '9946',
            self::RO_VAT => '9947',
            self::RS_VAT => '9948',
            self::SE_ORGNR => '0007',
            self::SG_UEN => '0195',
            self::SI_VAT => '9949',
            self::SK_DIC => '0245',
            self::SK_VAT => '9950',
            self::SM_VAT => '9951',
            self::TR_VAT => '9952',
            self::US_EIN => '9959',
            self::VA_VAT => '9953',
        };
    }
}
