import {observer} from "mobx-react-lite";
import {Toast, ToastContainer} from "react-bootstrap";
import {ErrorManager} from "../../domain/ErrorManager";

class ErrorsContainerProps {
  manager: ErrorManager;
}

export const ErrorsContainer = observer(({manager}: ErrorsContainerProps) => {
  if (!manager.hasErrors) {
    return null;
  }

  const error = manager.consumeError();

  console.log(error);
  return (
    <>
      <ToastContainer>
        <Toast>
          <Toast.Body>
            {error?.message}
          </Toast.Body>
        </Toast>
      </ToastContainer>
    </>
  )
})
