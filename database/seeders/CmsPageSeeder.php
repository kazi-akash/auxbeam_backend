<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CmsPage::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '
# About Auxbeam Bangladesh

## Our Story
Auxbeam Bangladesh is the official authorized distributor of Auxbeam® LED lighting products in Bangladesh. Auxbeam Lighting, founded in the United States, is a global leader in automotive LED lighting — trusted by off-road enthusiasts, truck owners, and automotive professionals worldwide.

## Our Mission
To bring premium Auxbeam LED lighting technology to Bangladeshi customers — delivering cutting-edge off-road lights, LED headlights, switch panels, and RGB lighting at competitive prices with local after-sales support.

## Our Vision
To be Bangladesh\'s most trusted destination for automotive LED lighting, known for genuine Auxbeam products, expert technical advice, and outstanding customer service.

## What We Offer
- **LED Light Bars**: 5D-PRO, 6 Modes Series, RGBW — from 3" to 50" in length
- **LED Headlights**: 7" round Jeep headlights, projector headlights, DOT-approved DRL lights
- **RGB Lighting**: Rock lights, whip lights, and RGB light bars with Bluetooth app control
- **Switch Panels**: AR Series 6/8-gang and KS-80 knob panel with app & remote control
- **LED Replacement Bulbs**: H4, H7, H11, 9005, 9006 and more
- **Wiring & Accessories**: Harnesses, mounting brackets, extension cables

## Our Values
- **Quality**: Only genuine Auxbeam® products — no counterfeits
- **Integrity**: Honest and transparent business practices
- **Customer First**: Your satisfaction is our top priority
- **Technical Excellence**: Expert support for installation and compatibility queries

## Contact Us
Phone: +880 1700-000001
Email: info@auxbeambd.com
                ',
                'meta_title' => 'About Us | Auxbeam Bangladesh',
                'meta_description' => 'Learn about Auxbeam Bangladesh — the official authorized distributor of Auxbeam® LED lighting products in Bangladesh.',
                'is_active' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '
# Contact Us

We\'d love to hear from you! Whether you have a question about our products, need installation help, or want to place a bulk order — we\'re here to help.

## Get in Touch

### Customer Service
- **Phone**: +880 1700-000001
- **Email**: support@auxbeambd.com
- **Hours**: Saturday - Thursday, 9 AM - 6 PM

### Sales & Bulk Orders
- **Phone**: +880 1700-000002
- **Email**: sales@auxbeambd.com

### Corporate & Fleet Orders
- **Email**: corporate@auxbeambd.com

## Visit Our Store

**Auxbeam Bangladesh**
123 Automotive Avenue, Gulshan
Dhaka 1212, Bangladesh

**Opening Hours**
- Saturday - Thursday: 10 AM - 9 PM
- Friday: 3 PM - 9 PM

## Follow Us
- Facebook: /auxbeambd
- Instagram: @auxbeambd
- YouTube: Auxbeam Bangladesh

## Feedback
Your feedback helps us improve. Please share your thoughts at feedback@auxbeambd.com
                ',
                'meta_title' => 'Contact Us | Auxbeam Bangladesh',
                'meta_description' => 'Get in touch with Auxbeam Bangladesh. Contact our customer service team for product questions, installation help, or order assistance.',
                'is_active' => true,
            ],
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'content' => '
# Frequently Asked Questions

## Orders & Shipping

### How long does delivery take?
- Dhaka: 3-5 business days
- Other cities: 5-7 business days
- International: 8-15 business days

### Do you offer free shipping?
Yes! Free shipping on orders above ৳1,700 (approx. $19.99 USD) within Bangladesh.

### Can I track my order?
Yes, you\'ll receive a tracking number via email once your order ships.

### Do you deliver outside Bangladesh?
Yes, we offer international shipping. Delivery time is 8-15 business days.

## Returns & Refunds

### What is your return policy?
You can return most items within 30 days of delivery. Items must be unused and in original packaging.

### How do I return an item?
Contact our customer service to get a Return Authorization Number, then ship the item back to us.

### When will I receive my refund?
Refunds are processed within 5-7 business days after we receive the returned item.

## Products

### Are all products genuine Auxbeam®?
Yes, we are an authorized Auxbeam® distributor. All products are 100% authentic.

### Do products come with warranty?
Yes, most Auxbeam products come with a manufacturer warranty. Check the product page for specific warranty terms.

### Can I get installation help?
Absolutely! Contact our technical support team for installation guidance and vehicle compatibility queries.

### Are LED light bars road legal in Bangladesh?
LED light bars are primarily designed for off-road use. Check local traffic regulations before fitting on public roads.

## Payment

### What payment methods do you accept?
- bKash
- Nagad
- Credit/Debit Cards (via SSL Commerz)
- Cash on Delivery

### Is online payment secure?
Yes, all online payments are processed through SSL-encrypted payment gateways.

## Account

### How do I create an account?
Click "Sign Up" and fill in your details. You can also checkout as a guest.

### I forgot my password. What do I do?
Click "Forgot Password" on the login page and follow the instructions sent to your email.

## Still have questions?
Contact us at support@auxbeambd.com or call +880 1700-000001
                ',
                'meta_title' => 'FAQ | Auxbeam Bangladesh',
                'meta_description' => 'Find answers to frequently asked questions about Auxbeam Bangladesh — orders, shipping, returns, installation, and product compatibility.',
                'is_active' => true,
            ],
            [
                'title' => 'Installation Guide',
                'slug' => 'installation-guide',
                'content' => '
# Installation Guide

## LED Light Bar Installation

### Required Tools
- Wire cutters / strippers
- Drill and drill bits
- Socket set / wrench
- Electrical tape or heat-shrink tubing
- Zip ties

### Basic Wiring Steps
1. Mount the light bar to your vehicle using the included brackets
2. Route the wiring harness from the battery to the light bar location
3. Connect the relay to the battery (positive and negative terminals)
4. Connect the switch wire to your preferred switch location
5. Connect the light bar connector to the harness output
6. Test the light before securing all wires

### Important Notes
- Always disconnect the battery before starting installation
- Use a fuse rated for your light bar\'s amperage
- Ensure all connections are weatherproof
- Do not exceed the rated voltage (9-32V DC)

## LED Headlight Installation (Jeep Round Headlights)

1. Remove the old headlight retaining ring
2. Disconnect the factory harness
3. Connect the H4-to-H13 adapter (included in package)
4. Seat the new LED headlight in the housing
5. Reconnect the retaining ring
6. Adjust beam angle as needed
7. For RGB halo models: connect the Bluetooth harness and download the Auxbeam app

## Switch Panel Installation (AR Series)

1. Choose a mounting location on your dash or console
2. Run the main power cable to the battery (use included fuse)
3. Ground the negative wire to chassis
4. Connect output wires to your auxiliary devices (lights, winch, etc.)
5. Power on and pair with the Auxbeam app via Bluetooth

## RGB Rock Light Installation

1. Clean mounting surfaces with alcohol wipe
2. Remove adhesive backing and press rock lights firmly onto surface
3. Route power cables through vehicle to battery area
4. Connect to the included Bluetooth controller
5. Download the Auxbeam app and pair via Bluetooth

## Need Help?
Contact our technical support team at support@auxbeambd.com or call +880 1700-000001
                ',
                'meta_title' => 'Installation Guide | Auxbeam Bangladesh',
                'meta_description' => 'Step-by-step installation guides for Auxbeam LED light bars, headlights, switch panels, and rock lights.',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            CmsPage::create($page);
        }

        $this->command->info('CMS pages seeded successfully!');
    }
}
