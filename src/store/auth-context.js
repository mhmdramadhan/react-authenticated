import React, { useState } from "react";

const AuthContext = React.createContext({
  token: "",
  isLoggedIn: false,
  login: (token) => {},
  logout: () => {},
});

export const AuthContextProvider = (props) => {
  const initialToken = localStorage.getItem("token");
  const [token, setToken] = useState(initialToken);

  // if true / jika token tidak kosong
  const userIsLoggedIn = !!token;

  const loginHandler = (token) => {
    setToken(token);

    localStorage.setItem("token", token);
  };

  const logoutHandler = () => {
    setToken(null);

    localStorage.removeItem("token");
  };

  const contexValue = {
    token: token,
    isLoggedIn: userIsLoggedIn,
    login: loginHandler,
    logout: logoutHandler,
  };

  return (
    <AuthContext.Provider value={contexValue}>
      {props.children}
    </AuthContext.Provider>
  );
};

export default AuthContext;
