import { useRef, useContext } from "react";
import { useHistory } from "react-router-dom";
import AuthContext from "../../store/auth-context";

import classes from "./ProfileForm.module.css";

const ProfileForm = () => {
  const newPasswordInputRef = useRef();
  const AuthCtx = useContext(AuthContext);
  const history = useHistory();

  const submitHandler = (event) => {
    event.preventDefault();

    const eneterdNewPassword = newPasswordInputRef.current.value;

    // add validation
    // end validation

    fetch(
      "https://identitytoolkit.googleapis.com/v1/accounts:update?key=AIzaSyBFe0hU6sbGD7fLygasNidK4yFTC3-UUeU",
      {
        method: "POST",
        body: JSON.stringify({
          idToken: AuthCtx.token,
          password: eneterdNewPassword,
          returnSecureToken: true,
        }),
        headers: {
          "Content-Type": "application/json",
        },
      }
    ).then((res) => {
      // assumption: Always succseds
      history.replace('/')
    });
  };

  return (
    <form className={classes.form} onSubmit={submitHandler}>
      <div className={classes.control}>
        <label htmlFor="new-password">New Password</label>
        <input
          type="password"
          minLength="7"
          id="new-password"
          ref={newPasswordInputRef}
        />
      </div>
      <div className={classes.action}>
        <button>Change Password</button>
      </div>
    </form>
  );
};

export default ProfileForm;
