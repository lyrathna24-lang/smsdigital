passport.use(new GoogleStrategy({
  clientID: "YOUR_CLIENT_ID",
  clientSecret: "YOUR_SECRET",
  callbackURL: "/auth/google/callback"
},
function(accessToken, refreshToken, profile, done) {
  return done(null, profile);
}));