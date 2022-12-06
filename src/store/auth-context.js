import React, { useState } from "react";

const AuthContext = React.createContext({
  token: "",
  isLoggedIn: false,
  login: (token) => {},
  logout: () => {},
});

export const AuthContextProvider = (props) => {
  const [token, setToken] = useState(null);

  // if true / jika token tidak kosong
  const userIsLoggedIn = !!token;

  const loginHandler = (token) => {
    setToken(token);
  };

  const logoutHandler = () => {
    setToken(null);
  };

  const contexValue = {
    token: token,
    isLoggedIn: userIsLoggedIn,
    login: loginHandler,
    logout: loginHandler,
  };

  return (
    <AuthContext.Provider value={contexValue}>
      {props.children}
    </AuthContext.Provider>
  );
};

export default AuthContext;
