<?php

namespace App\Modules\Payments\Services;

use App\Modules\Referrals\Models\Referral;
use App\Modules\Rewards\Models\CaregiverReward;

/**
 * Staff-facing payout row status — patient / referred-staff / account-mobile are separate AND gates.
 */
class StaffEarningStatusResolver
{
    public const PAID = 'paid';

    public const PAYABLE = 'payable';

    public const PENDING_PATIENT_OTP = 'pending_patient_otp';

    public const PENDING_REFERRAL_OTP = 'pending_referral_otp';

    public const HELD_ACCOUNT_MOBILE = 'held_account_mobile';

    /**
     * @return list<string>
     */
    public static function patientRewardBlockers(CaregiverReward $reward, bool $staffAccountMobileVerified): array
    {
        if ($reward->payment_processed) {
            return [self::PAID];
        }

        $blockers = [];
        if (! $reward->isPatientMobileOtpVerified()) {
            $blockers[] = self::PENDING_PATIENT_OTP;
        }
        if (! $staffAccountMobileVerified) {
            $blockers[] = self::HELD_ACCOUNT_MOBILE;
        }

        return $blockers === [] ? [self::PAYABLE] : $blockers;
    }

    /**
     * @return list<string>
     */
    public static function referralBlockers(Referral $referral, bool $staffAccountMobileVerified): array
    {
        if ($referral->payment_processed) {
            return [self::PAID];
        }

        $blockers = [];
        if (! $referral->isReferralMobileOtpVerified()) {
            $blockers[] = self::PENDING_REFERRAL_OTP;
        }
        if (! $staffAccountMobileVerified) {
            $blockers[] = self::HELD_ACCOUNT_MOBILE;
        }

        return $blockers === [] ? [self::PAYABLE] : $blockers;
    }

    public static function patientRewardPayoutStatus(CaregiverReward $reward, bool $staffAccountMobileVerified): string
    {
        $blockers = self::patientRewardBlockers($reward, $staffAccountMobileVerified);

        return self::primaryStatus($blockers);
    }

    public static function referralPayoutStatus(Referral $referral, bool $staffAccountMobileVerified): string
    {
        $blockers = self::referralBlockers($referral, $staffAccountMobileVerified);

        return self::primaryStatus($blockers);
    }

    public static function referralOtpVerified(Referral $referral): bool
    {
        return $referral->isReferralMobileOtpVerified();
    }

    /** Points/amount display only when both patient OTP and staff Profile mobile are done. */
    public static function patientRewardCountsForStaff(CaregiverReward $reward, bool $staffAccountMobileVerified): bool
    {
        return $reward->isPatientMobileOtpVerified() && $staffAccountMobileVerified;
    }

    public static function referralIncentiveCountsForStaff(Referral $referral, bool $staffAccountMobileVerified): bool
    {
        return $referral->isReferralMobileOtpVerified() && $staffAccountMobileVerified;
    }

    /**
     * @param  list<string>  $blockers
     */
    public static function primaryStatus(array $blockers): string
    {
        if (in_array(self::PAID, $blockers, true)) {
            return self::PAID;
        }
        if (in_array(self::PENDING_PATIENT_OTP, $blockers, true)) {
            return self::PENDING_PATIENT_OTP;
        }
        if (in_array(self::PENDING_REFERRAL_OTP, $blockers, true)) {
            return self::PENDING_REFERRAL_OTP;
        }
        if (in_array(self::HELD_ACCOUNT_MOBILE, $blockers, true)) {
            return self::HELD_ACCOUNT_MOBILE;
        }

        return self::PAYABLE;
    }

    public static function badgeLabel(string $status, bool $compact = false): string
    {
        return match ($status) {
            self::PAID => 'Paid',
            self::PAYABLE => $compact ? 'Payable' : 'Ready for payout',
            self::PENDING_PATIENT_OTP => $compact
                ? 'Patient mobile OTP pending'
                : 'Patient mobile OTP pending (WhatsApp to number on form)',
            self::PENDING_REFERRAL_OTP => $compact
                ? 'Referred staff OTP pending'
                : 'Referred staff mobile OTP pending',
            self::HELD_ACCOUNT_MOBILE => $compact
                ? 'Your Profile mobile not verified'
                : 'Your Profile mobile not verified (WhatsApp OTP)',
            default => 'Pending',
        };
    }

    public static function detailMessage(string $status, ?string $maskedPhone = null): ?string
    {
        return match ($status) {
            self::PENDING_PATIENT_OTP => $maskedPhone
                ? "Step 1: WhatsApp OTP required on patient mobile {$maskedPhone} (number entered on form — not your Profile mobile)."
                : 'Step 1: WhatsApp OTP required on the patient mobile entered on this form.',
            self::PENDING_REFERRAL_OTP => 'Step 1: The referred nurse/caregiver must complete WhatsApp OTP on their own mobile.',
            self::HELD_ACCOUNT_MOBILE => 'Step 2: Verify your own account mobile under Profile (WhatsApp OTP). Required in addition to patient/referral OTP before points pay out.',
            self::PAYABLE => null,
            self::PAID => null,
            default => null,
        };
    }

    /**
     * @param  list<string>  $blockers
     * @return list<string>
     */
    public static function detailMessagesForBlockers(array $blockers, ?string $maskedPhone = null): array
    {
        $messages = [];
        foreach ($blockers as $blocker) {
            if (in_array($blocker, [self::PAYABLE, self::PAID], true)) {
                continue;
            }
            $msg = self::detailMessage($blocker, $maskedPhone);
            if ($msg) {
                $messages[] = $msg;
            }
        }

        return $messages;
    }

    public static function patientRewardMaskedPhone(CaregiverReward $reward): ?string
    {
        if ($reward->verification_otp_sent_to) {
            return (string) $reward->verification_otp_sent_to;
        }

        $digits = preg_replace('/\D+/', '', (string) $reward->patient_phone);
        if (strlen($digits) < 4) {
            return null;
        }

        return 'Mobile: '.str_repeat('*', max(0, strlen($digits) - 4)).substr($digits, -4);
    }
}
