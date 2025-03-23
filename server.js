require('dotenv').config();
const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
const app = express();

// CORS configuration
const corsOptions = {
    origin: 'http://127.0.0.1:5500', // Allow your frontend to access the backend
    
    methods: ['GET', 'POST'],
    allowedHeaders: ['Content-Type']
};
app.use(cors(corsOptions));

// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// OTP storage
const otpStore = new Map();
const OTP_EXPIRY_TIME = 5 * 60 * 1000; // 5 minutes

// Email transporter
const transporter = nodemailer.createTransport({
    host: 'webmail.jyotirgamay.online',
    port: 25,
    secure: false, // No SSL
    auth: {
        user: process.env.EMAIL, // Your email address
        pass: process.env.EMAIL_PASSWORD, // Your email password
    },
});

// Email validation
function isValidEmail(email) {
    return /^[a-zA-Z0-9._%+-]+@jyotirgamay\.online$/.test(email);
}

// OTP Request Endpoint
app.post('/request-otp', async (req, res) => {
    try {
        const { email } = req.body;
        if (!email || !isValidEmail(email)) {
            return res.status(400).json({ message: 'Invalid email domain' });
        }

        const otp = Math.floor(100000 + Math.random() * 900000);
        otpStore.set(email, { 
            otp, 
            expiresAt: Date.now() + OTP_EXPIRY_TIME 
        });

        const mailOptions = {
            from: process.env.EMAIL,
            to: email,
            subject: 'Your OTP Code',
            text: `Your OTP code is ${otp}. It is valid for 5 minutes.`,
        };

        await transporter.sendMail(mailOptions);
        res.json({ message: 'OTP sent successfully' });

    } catch (error) {
        console.error('OTP Send Error:', error);
        res.status(500).json({ message: 'Error sending OTP' });
    }
});

// OTP Verification Endpoint
app.post('/verify-otp', (req, res) => {
    try {
        const { email, otp } = req.body;
        const storedData = otpStore.get(email);

        if (!storedData) {
            return res.status(400).json({ message: 'OTP expired or invalid' });
        }

        if (Date.now() > storedData.expiresAt) {
            otpStore.delete(email);
            return res.status(400).json({ message: 'OTP expired' });
        }

        if (storedData.otp === parseInt(otp)) {
            otpStore.delete(email);
            res.json({ message: 'OTP verified successfully' });
        } else {
            res.status(400).json({ message: 'Invalid OTP' });
        }

    } catch (error) {
        console.error('OTP Verify Error:', error);
        res.status(500).json({ message: 'Error verifying OTP' });
    }
});

// Server start
const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});




